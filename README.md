# important

## Dev/build configuration
- Add lines to the /etc/hosts file corresponding to the virtual hosts:

```
# you may want to change it, so be sure it is the same you added in the .env
127.0.0.1 app.tamplate.local 
127.0.0.1 db.tamplate.local
```
- Set up the configuration in the .env file
- Build the containers with the command:

```
    > make app-build
```

## Open the application in the browser with the  URL you have configured in the .env file