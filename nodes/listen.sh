#!/bin/bash

while true;
do
        result=$(nc -l -p 8000)
        firstcmd=`echo "${result}" | head -1`
        secondcmd=`echo "${result}" | head -2 | tail -1`
	    docker exec ndn sh -c "$firstcmd; $secondcmd"
done
exit 0

