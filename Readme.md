# Wikipedia Changes

This little php-script shows you the changes of a wikipedia article in charts.

## Installation
Go into the folder and create a image for the container:
```
    sudo docker build -t wiki .
```

And now create a container:
```
    sudo docker run -it -d --name=wiki -v /PATH/TO/THE/REPO/:/var/www/html -p 3001:80 wiki
```
You can delte the "-v" part, if you don't want a volume.

## License

MIT - 2021
