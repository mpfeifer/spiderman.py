#!/usr/bin/env python3

from time import time

import ipaddress
import logging
import random
import socket
import sys
import threading
import os

import sqlite3

logging.basicConfig(format='%(asctime)s|%(levelname)s|%(message)s',
                    filename='spiderman.log', level=logging.INFO)

log = logging.getLogger(__name__)

db_file = 'spiderman.db'

if not os.path.isfile(db_file):
    log.info(f"Initializing sqlite database in {db_file}")
    db_conn = sqlite3.connect(db_file)
    cursor = db_conn.cursor()
    cursor.execute('''
    CREATE TABLE IF NOT EXISTS `hosts` (
        id INTEGER PRIMARY KEY,
        ip TEXT NOT NULL,
        timestamp INTEGER NOT NULL,
        port INTEGER,
        hostname TEXT
    )
    ''')
    db_conn.close()

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
            'hostname': Tools.friendly_hostname(ipv4)
        }
    @staticmethod
    def randomIpv4():
        MAX_IPV4=ipaddress.IPv4Address._ALL_ONES
        while True:
            randomInt = random.randint(0, MAX_IPV4)
            if ((randomInt >= 2886729728) and (randomInt <= 2887778303) or
                (randomInt >= 3232235520) and (randomInt <= 3232301055) or
                (randomInt >=  167772160) and (randomInt <=  184549375)):
                continue
            else:
                break
        return ipaddress.IPv4Address._string_from_ip_int( randomInt )
    @staticmethod
    def ipv4Pool():
        while True:
            yield Factory.randomIpv4();

for target in Factory.ipv4Pool():
    hostDescriptor = None
    hostDescriptor = Factory.hostDescriptor(target, 80)
    ip=hostDescriptor['ipv4']
    log.info(f"Trying connection to host {ip}")
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        try:
            s.settimeout(5.0)
            s.connect((target, 80))
            s.shutdown(socket.SHUT_RDWR)
            log.info(f"Server found with ip {ip}")
            db_conn = sqlite3.connect(db_file)
            cursor = db_conn.cursor()
            data_tuple = (
                hostDescriptor['ipv4'],
                hostDescriptor['timestamp'],
                hostDescriptor['port'],
                hostDescriptor['hostname']
            )
            cursor.execute(sql, data_tuple)
            db_conn.commit()
            db_conn.close()
        except OSError:
            pass # ran into timeout
