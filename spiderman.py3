#!/usr/bin/env python3

import ipaddress
import random
import socket
from time import time
from MySQLdb import _mysql
import configparser
import sys

monitoredPorts = [ 80, 25, 8080 ]

def randomIpv4():
    MAX_IPV4=ipaddress.IPv4Address._ALL_ONES
    return ipaddress.IPv4Address._string_from_ip_int( random.randint(0, MAX_IPV4 ))

def genRandomIpv4():
    while True:
        yield randomIpv4();

def getServerDescriptor(ipv4, port):
    return {
        'ipv4': ipv4,
        'timestamp': time(),
        'port': port,
        'hostname': socket.gethostbyaddr(ipv4)[0]
    }

foundServers = []
noResponseHosts = []

dbhost, dgbuser, dbpasswd, dbname = None, None, None, None
try:
    parser=configparser.ConfigParser()
    parser.read("spiderman.conf")
    dbhost=parser.get("spiderman", "dbhost")
    dbuser=parser.get("spiderman", "dbuser")
    dbpasswd=parser.get("spiderman", "dbpasswd")
    dbname=parser.get("spiderman", "dbname")
except KeyError as e:
    print("Configuration file is missing values. Please configure dbhost, dbuser, dbpasswd and dbname")
    sys.exit(1)

sockets = []
for target in genRandomIpv4(): # using generator/yield yeah!
    if target.startswith("127.") or target.startswith("192."):
        continue
    foundServer = None
    print(f"{target}")
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        sockets.append(s)
        #        s.setblocking(0)
        s.settimeout(15.0)
        try:
            s.connect((target, 80))
            s.shutdown(socket.SHUT_RDWR)
            foundServer = getServerDescriptor(target, 80)
            db=_mysql.connect(host=dbhost, user=dbuser, passwd=dbpasswd, db=dbname)
            db.query(f"INSERT INTO active_hosts VALUES('{foundServer['ipv4']}', FROM_UNIXTIME({foundServer['timestamp']}), '{foundServer['port']}', '{foundServer['hostname']}')")
        except OSError:
            pass

    
