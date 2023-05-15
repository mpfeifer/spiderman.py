#!/usr/bin/env python3

import ipaddress
import random
import socket
from time import time
from MySQLdb import _mysql
import configparser
import sys

class Tools:

    @staticmethod
    def friendly_hostname(ipv4):
        try: 
            (hostname, aliaslist, ipadrlist) = socket.gethostbyaddr(ipv4);
            if hostname:
                return hostname
        except:
            pass
        return ""

class Factory:

    @staticmethod
    def hostDescriptor(ipv4, port):
        return {
            'ipv4': ipv4,
            'timestamp': time(),
            'port': port,
            'hostname': Tools.friendly_hostname(ipv4),
            'active': False
        }

    @staticmethod
    def randomIpv4():
        MAX_IPV4=ipaddress.IPv4Address._ALL_ONES
        return ipaddress.IPv4Address._string_from_ip_int( random.randint(0, MAX_IPV4 ))

    @staticmethod
    def ipv4Pool():
        while True:
            yield Factory.randomIpv4();

foundServers = []
noResponseHosts = []
sockets = []
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

for target in Factory.ipv4Pool():

    if target.startswith("127.") or target.startswith("192.") or target.startswith("10."):
        continue

    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        hostDescriptor = None
        sockets.append(s)
        s.settimeout(15.0)
        hostDescriptor = Factory.hostDescriptor(target, 80)
        try:
            s.connect((target, 80))
            s.shutdown(socket.SHUT_RDWR)
            hostDescriptor['active']=True
        except OSError:
            pass
        db=_mysql.connect(host=dbhost, user=dbuser, passwd=dbpasswd, db=dbname)
        db.query(f"INSERT INTO hosts VALUES('{hostDescriptor['ipv4']}', FROM_UNIXTIME({hostDescriptor['timestamp']}), '{hostDescriptor['port']}', '{hostDescriptor['hostname']}', '{int(hostDescriptor['active'])}')")

    
