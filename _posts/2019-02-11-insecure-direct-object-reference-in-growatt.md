---
layout: post
title: "Insecure direct object reference in Growatt"
thumbnail: solarpanel-480.jpg
date: 2019-08-28
---

An insecure direct object reference in the Growatt API to retrieve data on solar panels makes it possible to retrieve information on other users.

<!-- photo source: https://pixabay.com/en/photovoltaic-solar-system-energy-2814504/ -->

## Solar plant data 

At our scouting clubhouse we have solar panels on the roof. These are connected with a Wi-Fi dongle to the internet so that you can view the amount of generated power through the [Growatt](https://server.growatt.com/) web site. In order to integrate this data into another application I created an Python [API client](https://github.com/Sjord/growatt_api_client) that retrieves the data on the solar panels.

While looking for an API endpoint that provides higer resolution data, I mailed someone a link that worked for me:

    https://server.growatt.com/newPlantDetailAPI.do?plantId=23528&date=2019-02-09&type=1

He reported back that he could see the data from my plant, which at least seemed remarkable. It turns out that you can see data from any plant my modifying the `plantId` parameter. This is a classic insecure direct object reference. The `plantId` contains the number to retrieve. While you have to be logged in, there is no further authorization check whether you are the owner of this plant.

## Conclusion

This vulnerability was found by accident when sharing a link.