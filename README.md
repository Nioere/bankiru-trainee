## Installation and Setup

Start docker
```shell
make up
```

Install composer dependencies
```shell
make composer
```

Perform migrations
```shell
make migrate
```

Create the database for the tests
```shell
make test-create
```

Migrate the test database
```shell
make test-migrate
```

**OpenApi method schema is available at http://localhost:8080/**


Run api tests:
```shell
make tests-api
```

The command to output the list of Rick and Morty characters (`src/Command/GetNamesCommand.php`)
```shell
bin/console get_names 1
```

File list output command (`src/Command/ShowFilesCommand.php`)
```shell
bin/console show_files
```

Combat simulation command (`src/Command/ShowFilesCommand.php`)
```shell
bin/console fight
```

