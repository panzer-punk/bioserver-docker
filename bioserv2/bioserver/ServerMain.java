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

import java.io.IOException;
import java.util.Arrays;
import java.util.Date;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Main class for initialisation
 */
public class ServerMain {
    // the server listens on this port
    public final static int LOBBYPORT = 8200;
    public final static int GAMEPORT  = 8590;	// if you change this, search for gs_info
    public final static String GAMEID = "00000010";
    
    // Entry point
    public static void main(String[] args) {

        // setup the packethandler in his own thread
        PacketHandler packethandler = new PacketHandler();
        new Thread(packethandler).start();

        // create the lobbyserver thread
        ServerThread server = new ServerThread(null, LOBBYPORT, packethandler);
        new Thread(server).start();
        
        // create a simple gameserver
        GameServerPacketHandler packethandler2 = new GameServerPacketHandler();
        new Thread(packethandler2).start();
        GameServerThread gsserver = new GameServerThread(null, GAMEPORT, packethandler2);
        new Thread(gsserver).start();
        
        // allow usage
        packethandler.setGameServerPacketHandler(packethandler2);
        
        try {
            Thread.sleep(1000);
        } catch (InterruptedException ex) {
            Logger.getLogger(ServerMain.class.getName()).log(Level.SEVERE, null, ex);
        }
        
        // last but not least
        // create a thread for the keepalivepings and cleanups
        new Thread(new HeartBeatThread(server, packethandler, gsserver, packethandler2)).start();
        
        System.out.println("------------------------------\n"+
                           "-     fanmade server for     -\n"+
                           "- Biohazard Outbreak File #2 -\n"+
                           "------------------------------\n");

        Date date = new Date();
        System.out.println(date.toString()+" server started");
    }
}