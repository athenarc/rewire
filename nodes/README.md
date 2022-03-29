### Network Nodes
At each network node execute the following commands:

```
./listen.sh &
./nodeinfo.sh &
python3 bestorigin.py
```
In addition, at the NDN Consumer node run the consumer.sh script in order to start sending Interest packets. 
```
./consumer.sh &
```
