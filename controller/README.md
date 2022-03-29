### SDN Controller
The centralized monitoring of the wireless mesh network is performed with the following scripts:

```
./getNodes.sh           # collect information about the network nodes
./getOriginators.sh     # collect the best originators
```

At the Controller node execute the following commands:

```
python3 controller.py
```
