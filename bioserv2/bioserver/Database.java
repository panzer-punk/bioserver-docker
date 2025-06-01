/*
    BioServer2 -Emulation of the long gone server for 
                Biohazard Outbreak File #2 (Playstation 2)

    Copyright (C) 2013-2019 obsrv.org (no23@deathless.net)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published
    by the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

package bioserver;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.io.UnsupportedEncodingException;
import java.nio.ByteBuffer;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.LinkedList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * class for managing the bio2fog database
 */
public class Database {
    private String url;
    private String user;
    private String password;
    private String host;
    private String params;
    private String database;
    
    private Connection con = null;
    
    // simple constructor to create a reusable connection to the database
    public Database(
        String db_user, 
        String db_password,
        String db_host,
        String db_params,
        String db_database
    ) {
        this.user = db_user;
        this.password = db_password;
        this.host = db_host;
        this.params = db_params;
        this.database = db_database;
        this.url = String.format(
            "jdbc:mysql://%s:3306/%s?useUnicode=true&characterEncoding=UTF-8%s",
            this.host,
            this.database,
            this.params
        );
        
        try {
            con = (Connection) DriverManager.getConnection(url, user, password);
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        // setup some things on server restart
        // set area to -1, rooms, slots and gamesessions to 0
        this.setupDBrestart();
    }
    
    
    // test connection and create a new one on failure
    private void testConnection() {
        PreparedStatement pst = null;
        ResultSet rs = null;
        
        try {
            pst = (PreparedStatement) con.prepareStatement("select 1;");
            rs = pst.executeQuery();
        } catch (SQLException ex) {
            try {
                if (rs != null) {
                    rs.close();
                }
                if (pst != null) {
                    pst.close();
                }
                if (con != null) {
                    con.close();
                }            
            } catch (SQLException ex2) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex2);
            }
            // create a new connection
            try {
                this.con = (Connection) DriverManager.getConnection(url, user, password);
            } catch (SQLException ex1) {
                Logger.getLogger(Database.class.getName()).log(Level.SEVERE, null, ex1);
            }
        }
    }

    
    // get userid of an existing session
    public String getUserid(String sessid) {
        // check it out ;-)
        this.testConnection();
        
        PreparedStatement pst = null;
        ResultSet rs = null;
        String retval = "";
        try {
            pst = (PreparedStatement) con.prepareStatement(String.format("select userid from sessions where sessid='%s' and gameid = '%s'", sessid, ServerMain.GAMEID));
            rs = pst.executeQuery();
            if(rs.next()){
                retval = rs.getString("userid");            
            }
            
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
        
        return retval;
    }
    
    // get handle/nickname list for a given userid
    public HNPairs getHNPairs(String userid) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        HNPairs hns = new HNPairs();
        String nickname;
        
        try {
            pst = (PreparedStatement) con.prepareStatement(String.format("select handle,nickname from hnpairs where userid='%s' limit 0,3", userid));
            rs = pst.executeQuery();
            while(rs.next()){
                hns.add(new HNPair(rs.getString("handle"), rs.getString("nickname")));
            }
            
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
        
        return hns;
    }
    
    // check if a handles exists
    // returns true when handle is free
    public boolean checkHandle(String handle) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        boolean retval = false;
        
        try {
            pst = (PreparedStatement) con.prepareStatement(String.format("select count(*) as cnt from hnpairs where handle='%s'", handle));
            rs = pst.executeQuery();
            if(rs.next()){
                if(rs.getInt("cnt") == 0) retval=true;
            }
            
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
        
        return retval;        
    }
    
    // insert a new handle/nickname into database
    public void createNewHNPair(Client cl) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        try {
            String nickname;
            try {
                nickname = new String(cl.getHNPair().getNickname(),"SJIS");
            } catch (UnsupportedEncodingException ex) {
                nickname = "sjis";
            }
            pst = (PreparedStatement) con.prepareStatement(String.format("insert into hnpairs (userid,handle,nickname) values ('%s','%s','%s')", 
                    cl.getUserID(), new String(cl.getHNPair().getHandle()), nickname));
            pst.executeUpdate();
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
    }
    
    // update handle/nickname of a client
    public void updateHNPair(Client cl) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        String nickname;
        try {
            nickname = new String(cl.getHNPair().getNickname(),"SJIS");
        } catch (UnsupportedEncodingException ex) {
            nickname = "sjis";
        }

        try {
            pst = (PreparedStatement) con.prepareStatement(String.format("update hnpairs set nickname='%s' where userid='%s' and handle='%s'", 
                    nickname, cl.getUserID(), new String(cl.getHNPair().getHandle())));
            pst.executeUpdate();
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }        
    }

   
    // set area, room, slot for a user
    private void setupDBrestart() {
        PreparedStatement pst = null;
        ResultSet rs = null;
        try {
            pst = (PreparedStatement) con.prepareStatement("update sessions set area=-1, room=0, slot=0, gamesess=0, state=0 where gameid=?");
            pst.setString(1, ServerMain.GAMEID);
            pst.executeUpdate();
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
    }

    // set area, room, slot for a user and a state
    public void updateClientOrigin(String userid, int state, int area, int room, int slot) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        try {
            pst = (PreparedStatement) con.prepareStatement("update sessions set state=?, area=?, room=?, slot=? where userid=? and gameid=?");
            pst.setInt(1, state);
            pst.setInt(2, area);
            pst.setInt(3, room);
            pst.setInt(4, slot);
            pst.setString(5, userid);
            pst.setString(6, ServerMain.GAMEID);
            pst.executeUpdate();
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
    }


    // set game for a user
    public void updateClientGame(String userid, int gamenumber) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        try {
            String gamenum = (new Integer(gamenumber)).toString();      // TODO: I hate Java for this!
            pst = (PreparedStatement) con.prepareStatement(String.format("update sessions set gamesess='%s' where userid='%s' and gameid='%s'", gamenum, userid, ServerMain.GAMEID));
            pst.executeUpdate();
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
    }


    // get the gamenumber of a given userid
    public int getGameNumber(String userid) {
        PreparedStatement pst = null;
        ResultSet rs = null;
        int retval = 0;
        try {
            pst = (PreparedStatement) con.prepareStatement(String.format("select gamesess from sessions where userid='%s' and gameid='%s'", userid, ServerMain.GAMEID));
            rs = pst.executeQuery();
            if(rs.next()){
                retval = rs.getInt("gamesess");            
            }
            
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
        
        return retval;
    }


    // get message of the day
    public String getMOTD() {
        // check it out ;-)
        this.testConnection();
        
        PreparedStatement pst = null;
        ResultSet rs = null;
        String retval = "";
        try {
            pst = (PreparedStatement) con.prepareStatement("select message from motd where active=1 order by id desc limit 0,1");
            rs = pst.executeQuery();
            if(rs.next()){
                retval = rs.getString("message");            
            }
            
        } catch (SQLException ex) {
            Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
        } finally {
            try {
                if(rs != null) {
                    rs.close();
                }
                if(pst != null) {
                    pst.close();
                }
            } catch (SQLException ex) {
                Logger.getLogger(Database.class.getName()).log(Level.WARNING, null, ex);
            }
        }
        
        return retval;
    }    


}
