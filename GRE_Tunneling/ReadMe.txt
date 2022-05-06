The above script creates a GRE Tunneling between 2 nodes are
located in different networks, targeting the creation of a 
local communication between them. This approach was used to
make the SDN Controller perceive the nodes from the federated
testbeds as a single local area network.

The following example describes a GRE Tunneling between two network nodes by using the GreTunneling script.

linux command:
./GreTunneling 195.251.209.205 155.98.38.124 rewire 10.2.0.250 10.1.0.0/24 2 10.2.0.1

According to the above command, the following arguments have been introduced:

RealRemoteAddress=195.251.209.205
RealLocalAddress=155.98.38.124
TunnelDevice= rewire
LocalAddress=10.2.0.25
RemotePrivateIP=10.2.0.250
NodesNumber=2
LocalPrivateIP=10.2.0.1
