#!/bin/sh
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<-EOSQL
    SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
    SET time_zone = "+00:00";

    CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
    GRANT ALL PRIVILEGES ON \`$DB_DATABASE\`.* TO '$MYSQL_USER'@'%'; 

    USE \`$DB_DATABASE\`;

    CREATE TABLE IF NOT EXISTS \`sessions\` (
      \`userid\` varchar(14) NOT NULL,
      \`ip\` varchar(15) NOT NULL,
      \`port\` int(11) NOT NULL,
      \`sessid\` varchar(8) NOT NULL,
      \`gamesess\` bigint(20) NOT NULL DEFAULT '0',
      \`lastlogin\` timestamp NULL DEFAULT NULL,
      \`area\` int(11) DEFAULT '-1',
      \`room\` int(11) DEFAULT '0',
      \`slot\` int(11) DEFAULT '0',
      \`state\` int(11) DEFAULT '0',
      \`gameid\` varchar(8) NOT NULL,
      KEY \`gamesess\` (\`gamesess\`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;


    CREATE TABLE IF NOT EXISTS \`hnpairs\` (
      \`userid\` varchar(14) NOT NULL,
      \`handle\` varchar(6) NOT NULL,
      \`nickname\` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
      KEY \`userid\` (\`userid\`),
      KEY \`handle\` (\`handle\`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;


    CREATE TABLE IF NOT EXISTS \`motd\` (
      \`id\` int(11) NOT NULL AUTO_INCREMENT,
      \`message\` varchar(2000) NOT NULL,
      \`active\` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (\`id\`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;


    CREATE TABLE IF NOT EXISTS \`users\` (
      \`userid\` varchar(14) NOT NULL,
      \`passwd\` varchar(32) NOT NULL,
      PRIMARY KEY (\`userid\`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

    exit
EOSQL

