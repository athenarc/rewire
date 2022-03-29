# REWIRE: Experimenting with SDN-based Adaptable Non-IP Protocol Stacks in Smart-City Environments


![REWIRE project overview](/rewire.jpg)

## Description

REWIRE tackles scalability and reliability issues in Smart-Cities through an SDN-based management platform that mixes-and-matches, on-demand, multiple Non-IP protocol strategies with real (i) rapidly-detected network conditions, and (ii) IoT data communication patterns.

In this repository we provide the main scripts of the REWIRE project including:

*
*
*

## Example
The network deployments in the REWIRE project include:
*The SDN Controller: Instructions about running the implemented SDN Controller can be found at Readme file [here](/controller/readme.md) 
*The Network Nodes: Instructions about running the Network Nodes can be found at Readme file [here](/nodes/readme.md) 

### Controller
At the Controller node execute the following commands:

```
python3 controller.py
```

### Network Nodes
At each network node execute the following commands:

```
./listen.sh &
./nodeinfo.sh &
python3 bestorigin.py
```
In addition, at the NDN Consumer node run the consumer.sh script in order to start sending Interest packets. 
```

```

