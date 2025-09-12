#!/usr/bin/env python3

from MySQLdb import _mysql
from time import time

import configparser
import ipaddress
import random
import socket
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
        while True:
            randomInt = random.randint(0, MAX_IPV4)
            if ((randomInt >= 2886729728) && (randomInt <= 2887778303) or
                (randomInt >= 3232235520) && (randomInt <= 3232301055) or
                (randomInt >=  167772160) && (randomInt <=  184549375)):
                continue
            else:
                break
        return ipaddress.IPv4Address._string_from_ip_int( randomInt )

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
    parser.read("conf/spiderman.conf")
    dbhost=parser.get("spiderman", "dbhost")
    dbuser=parser.get("spiderman", "dbuser")
    dbpasswd=parser.get("spiderman", "dbpasswd")
    dbname=parser.get("spiderman", "dbname")
except KeyError as e:
    print("Configuration file is missing values. Please configure dbhost, dbuser, dbpasswd and dbname")
    sys.exit(1)

for target in Factory.ipv4Pool():

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
