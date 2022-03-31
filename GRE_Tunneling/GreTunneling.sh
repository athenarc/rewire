#!/bin/bash
args=("$@")

RealRemoteAddress="${args[0]}"
RealLocalAddress="${args[1]}"
TunnelDevice="${args[2]}"
LocalAddress="${args[3]}"
RemoteAddress="${args[4]}"
NodesNumber="${args[5]}"
PrivateNodeIPs="${args[6]}"


#configure Tunneling

sudo ip tunnel add $TunnelDevice mode gre remote $RealRemoteAddress local $RealLocalAddress ttl 255
sudo ip link set $TunnelDevice up
sudo ip addr add $LocalAddress dev $TunnelDevice
sudo ip route add $RemoteAddress dev $TunnelDevice

#configuring routing table

InterfaceName=$(ifconfig | grep -B1 "inet addr:$PrivateNodeIPs" | awk '$1!="inet" && $1!="--" {print $1}')

for i in `seq 2 $(($NodesNumber+2))`;
do
        sudo ip route add 10.3.`echo $i`.0/24 via 10.2.0.`echo $i` dev $InterfaceName
done
