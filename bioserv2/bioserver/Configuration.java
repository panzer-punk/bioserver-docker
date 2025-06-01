/*
    BioServer - Emulation of the long gone server for 
                Biohazard Outbreak File #1 (Playstation 2)

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

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *  Configuration
 *  reads config.properties in the same directory folder
 */
public class Configuration {
    public String gs_ip;
    public String db_user;
    public String db_password;
    public String db_host;
    public String db_params;
    public String db_database;

    // constructor
    public Configuration() {
        this.gs_ip       = System.getenv("SERVER_IP");
        this.db_user     = System.getenv("DB_USER");
        this.db_password = System.getenv("DB_PASSWORD");
        this.db_host     = System.getenv("DB_HOST");
        this.db_params   = System.getenv("JAVA_DB_PARAMS");
        this.db_database = System.getenv("DB_DATABASE");
    }
    
}
