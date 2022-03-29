The provided bash script has been developed in order to facilitate the NDN protocol management and configuration.

We assume that the NDN daemon is running in a container named **ndn

#Usage
Commands for adding or deleting an NDN face:
```
./ndnconf ndnaddface <content prefix> <face>
./ndnconf ndndelface <content prefix> <face>

```
```
./ndnconf ndnregister <content prefix> <face>
./ndnconf ndnunregister <content prefix> <face>

```
