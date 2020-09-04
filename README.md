## How to install
1. change `.env` file
2. run `npm install`
3. start containers with `docker-compose up -d`
4. done!


## TODO
1. Files & Folders

    1.1. File tagging system

    1.2. File expiration system

    1.3. File & folder sharing, renaming, permadelete

    1.4. Nested folder zipping

    1.5. Trash folder

    1.6. More dynamic context menu
2. UI

    2.1. UI breadcrumbs

    2.2. Simpler UI, visual upgrades (alternate UI selection?)

    2.3. Personalization (backgrounds, colors etc.)

    2.4. Loading and upload progress animations
3. Refactoring

    3.1. Merge files and folders into one model entity
4. Bugs

    4.1. Archives, Files don't delete

    4.2. File icons don't show up on upload but on refresh (see comment in `FolderComponent.vue`, before 'thumbnail' method)

    4.3. Fix file order on upload
