#!/usr/bin/python3

from MySQLdb import _mysql
import socket

db=_mysql.connect( host="localhost", user="matthias", passwd="matthias", db="mdb" )
db.query( "SELECT * from active_hosts where hostname is null" )
rows=db.store_result().fetch_row(maxrows=0)
for row in rows:
    ipAsString=row[0].decode("utf-8")
    hostInfo = None
    hostname=""
    try:
        hostInfo=socket.gethostbyaddr(ipAsString)
        hostname=hostInfo[0]
    except socket.herror as e:
        pass
    db.query(f"UPDATE active_hosts SET hostname='{hostname}' WHERE ip='{ipAsString}'")


