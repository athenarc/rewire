#!/bin/bash

NDNCheck=$(docker ps | grep ndn)


ndnpeek()
{
    #first check if NDN container is running
    if [ ! -z "$NDNCheck" ]
    then
        #docker exec ndn ndnpeek -f -w 1000 -v $content
        docker exec ndn sh -c "ndnpeek -f -w 1000 -v $content | ndn-dissect"
    else 
        echo "ERROR no running container found"
        exit 1
    fi
    
    exit 0
}

ndnregister()
{
    #first check if NDN container is running
    if [ ! -z "$NDNCheck" ]
    then
        docker exec ndn nfdc register -e 10000  $content $face
    else 
        echo "ERROR no running container found"
        exit 1
    fi
    
    exit 0
}

ndnunregister()
{
    #first check if NDN container is running
    if [ ! -z "$NDNCheck" ]
    then
        docker exec ndn nfdc unregister $content $face
    else 
        echo "ERROR no running container found"
        exit 1
    fi

    exit 0
}

ndnaddface()
{
    #first check if NDN container is running
    if [ ! -z "$NDNCheck" ]
    then
        docker exec ndn nfdc create $face
    else 
        echo "ERROR no running container found"
        exit 1
    fi
    
    exit 0
}

ndndelface()
{
    #first check if NDN container is running
    if [ ! -z "$NDNCheck" ]
    then
        docker exec ndn nfdc destroy $face
    else 
        echo "ERROR no running container found"
        exit 1
    fi
    
    exit 0
}


case "$1" in
      ndnregister)
        content=$2
        face=$3
        ndnregister
        ;;
    ndnunregister)
        content=$2
        face=$3
        ndnunregister
        ;;
    ndnaddface)
        face=$2
        ndnaddface
        ;;  
    ndndelface)
        face=$2
        ndndelface
        ;;
    ndnpeek)
        content=$2
        ndnpeek
        ;;
    *)    
    echo "Syntax Error:"
    exit 1
esac

exit 0
