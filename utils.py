#!/bin/env python
'''
 @brief         All decorator functions.
 @author        kevin.liu@century21cn.com
 @copyright (C) 2016 Century21cn All Rights Reserved.
 @version       1.0
 @date     2016-09-11
'''
import pdb
import time
import json
import httplib
import socket
import datetime
import csv
import types
import random
import hashlib
from django.http import HttpResponseRedirect
from django.conf import settings
from django.shortcuts import render_to_response
from django.template import RequestContext
from django.http import HttpResponse
from django.utils import simplejson
import logging
logger = logging.getLogger('django')
error_logger = logging.getLogger('error')


def render_template(template, **kwargs):
    new_kwargs = {"settings": settings}
    if kwargs.has_key("settings"):
        kwargs.pop("settings")
    if kwargs.has_key("request"):
        request = kwargs["request"]
    if not kwargs.has_key("current_nav"):
        kwargs["current_nav"] = None
    new_kwargs.update(kwargs)
    
    if request:
        new_kwargs['username'] = request.session.get("username",False)
        instance = RequestContext(request)
        return render_to_response(template, new_kwargs, context_instance = instance)
    
    return render_to_response(template, new_kwargs)


'''
 @brief         render http response to json decorator
 @author        kevin.liu@century21cn.com
 @copyright (C) 2016 Century21cn All Rights Reserved.
 @version       1.0
'''
def render_json(view_func):
  
    def wrap(request, *args, **kwargs):
        #pdb.set_trace()
        retval = view_func(request, *args, **kwargs)
        if isinstance(retval, HttpResponse):
            retval.mimetype = 'application/json'
            return retval
        else:
            json = simplejson.dumps(retval)
            return HttpResponse(json, mimetype='application/json')
    return wrap

'''
 @brief         limit control 
 @author        kevin.liu@century21cn.com
 @copyright (C) 2016 Century21cn All Rights Reserved.
 @version       1.0
'''
def noc_permission(perm=None):
    def check_login(request):
        if request.session.get('username', False):
            return request.session.get('username')
        return False
    return user_passes_test(check_login, perm=perm)


def user_passes_test(test_func, perm=None):
    def decorator(view_func):
        def _wrapped_view(request, *args, **kwargs):
            login_back = HttpResponseRedirect("/admin/login/")
            green_card = view_func(request, *args, **kwargs)
            if not test_func(request):
                return login_back
            user_name = test_func(request)
            if user_name:
                return green_card

            return  login_back

        return _wrapped_view

    return decorator

'''
 @class         Class HTTPConnectionWithTimeout
 @brief         connect web address
 @author        kevin.liu@century21cn.com
 @copyright (C) 2016 Century21cn All Rights Reserved.
 @version       1.0
'''
class HTTPConnectionWithTimeout(httplib.HTTPConnection):
    timeout = 30

    def connect(self):
        msg = "getaddrinfo returns an empty list"
        for res in socket.getaddrinfo(self.host, self.port, 0, socket.SOCK_STREAM):
            af, socktype, proto, canonname, sa = res
            try:
                self.sock = socket.socket(af, socktype, proto)
                try:
                    self.sock.settimeout( float(self.timeout) ) # default self.timeout is an object() in 2.6
                except:
                  pass
                self.sock.connect(sa)
                self.sock.settimeout(None)
            except socket.error as e:
                msg = e
                if self.sock:
                    self.sock.close()
                    self.sock = None
                    continue
                break
            if not self.sock:
                raise socket.error(msg)

'''
 @class         Class metricTreeSocket
 @brief         connect web address
 @author        kevin.liu@century21cn.com
 @copyright (C) 2016 Century21cn All Rights Reserved.
 @version       1.0
'''
class metricTreeSocket(object):

    def __init__(self, host, port ):
        self.port = port
        self.host = host
        self.SOCKET_TEMPLATE = "finder __CONTENT__\n"

    def RecvN(self,socket, n, timeout,message):   
        totalContent = ""
        begin=time.time()
        while True:
            if time.time() - begin > timeout:
                error_logger.info( 'Socket get data timeout use_time:'+str(time.time()-begin))
                break
            try:
                buf = socket.recv(n)
                begin = time.time()
            except Exception,e:
                error_logger.info( 'Socket get data error '+str(socket)+"error message:"+str(e)+" key:"+message)
            totalContent += buf
            if "}" in buf:
                break
        return totalContent

    def client_metricTree(self,transQuery):
        time1 = time.time()

        try:
            new_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            new_socket.settimeout(12)
        except socket.error, msg:
            error_logger.info( 'Failed to creat socket. Error code:'+str(msg[0]) )
            error_logger.info( 'Error message:'+msg[1] )
            return {}
        host = self.host
        port = self.port

        try:
            remote_ip = socket.gethostbyname(host)
        except socket.gaierror:
            error_logger.info( 'Hostname could not be resolved. Exiting.'+'Ip address of '+ host +':'+port  )
            return {}
      
        try:
            new_socket.connect((host, port))
        except:
            error_logger.info(  'Socket Connected error '+ host +' on ip '+remote_ip  )
            new_socket.close()
            return {}
       
		message = self.SOCKET_TEMPLATE.replace("__CONTENT__",transQuery)

        try:
            new_socket.sendall(message)
        except socket.error:
            error_logger.info( 'Send fail. message: '+ message )
            new_socket.close()
            return {}
        try:
          
            totalContent = ""
            begin=time.time()
            while True:
                if time.time() - begin > 10:
                    error_logger.info( 'Socket get data timeout use_time:'+str(time.time()-begin))
                    break
                try:
                    buf = new_socket.recv(1024)
                    begin = time.time()
                    if not buf:
                        error_logger.info( 'Socket shutDown break message:'+message)
                        break
                    totalContent += buf
                except Exception,e:
                    error_logger.info( 'Socket get data error '+str(socket)+"error message:"+str(e)+" key:"+message)
                    new_socket.close()
                    return {}
                if "}" in buf[-3:]:
                    break
            if not totalContent:
                totalContent = "{}"
            response_data = json.loads(totalContent)
        except Exception,e:
            error_logger.info( 'reply socket error. reply:'+ message +" error Info:"+str(e))
            new_socket.close()
            response_data = {}
       
        new_socket.close()
        time2 = time.time()
        logger.info( 'socket to C++ get metricTree message:'+ message +' use_time: '+ str(time2-time1))
        return response_data