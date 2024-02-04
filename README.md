## Запуск проекта
Запускаем docker
```shell
make up
```

Устанавливаем зависимости composer
```shell
make composer
```

Выполняем миграции
```shell
make migrate
```

OpenApi схема методов доступна по ссылке http://localhost:8080/

Запуск api тестов:
```shell
make tests-api
```

Команда для вывода списка персонажей рик и морти (`src/Command/GetNamesCommand.php`)
```shell
bin/console get_names 1
```

Команда с выводом списка файлов (`src/Command/ShowFilesCommand.php`)
```shell
bin/console show_files
```

Команда для имитации боя (`src/Command/ShowFilesCommand.php`)
```shell
bin/console fight
```
