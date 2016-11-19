---
layout: post
title: "Adding a HTTP header to Nikto requests"
thumbnail: radio-dish-240.jpg
date: 2016-11-28
---



nl=$'\n'
cr=$'\r'
nikto -nossl -host http://172.16.122.131:8912/ -useragent "hello world${cr}${nl}Some: haeder"
