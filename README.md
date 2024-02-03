# Silo: Modular Storage Connectors

### Laravel Package for transparently accessing different storage providers with a unified interface.

Currently, supports:

- Google Drive
- Confluence

## Installation

You can install the package via composer:

```bash
composer require jscott-rawnet/silo-storage-connectors
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="silo-storage-connectors-config"
```

This is the contents of the published config file:

```php
return [
    'google_drive' => [
        'service_account' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],
    'confluence' => [
        'username' => env('CONFLUENCE_USERNAME'),
        'api_token' => env('CONFLUENCE_API_TOKEN'),
        'domain' => env('CONFLUENCE_DOMAIN'),
    ],
];
```

## Usage

### Google Drive

For Google Drive you will require a Google Service account that has permission to view the files you're trying to access, or you will receive errors.

Once created, download the credentials json file and place it in the root of your project dir. You will then need to add the name of the file to your `.env` file.

You can get a single file from Google Drive using the `get` method.

```php 
GoogleDriveSilo::get('FILEID', includeFileContent: false);
```

You can also get a list of files from Google Drive using the `list` method.

```php
GoogleDriveSilo::list(extraArgs: [
    'q' => [
        "trashed" => false, 
        "'PARENT_FOLDER_ID' in parents"
    ], 
    "pageSize" => 5, 
    "pageToken" => null, //Next page token from response
], includeFileContent: false);
```

### Confluence
The Confluence connector treats 'Pages' as 'Files' - You can get a single 'File' from Confluence using the `get` method.

```php
ConfluenceSilo::get('PAGE_ID', includeFileContent: false);
```

You can also get a list of 'Files' from Confluence for a particular Space using the `list` method.

```php
ConfluenceSilo::list(extraArgs: [], includeFileContent: true, spaceId: 'SPACE_ID');
```

### General

#### Env Variables:
Each connector will require different env variables to be set in your project in order to authenticate with the respective service, these are detailed in the config file.

#### Include File Content:
For performance, by default all methods will return a SiloFile object which contains the file metadata but not the file contents. If you do require the file contents, you can set `includeFileContent` to `true` and it will be available on the `->contentStream` method as a PSR7 Stream.

```php
ConfluenceSilo::get('PAGE_ID', includeFileContent: true)->contentStream();
```

#### Extra Args:
Every connector `list` method will allow you to pass an array of `extraArgs` the params you can pass here are determined by the service the connector is communicating with.

For Google Drive, See:
[https://developers.google.com/drive/api/reference/rest/v3/files/list](https://developers.google.com/drive/api/reference/rest/v3/files/list)

For Confluence, See (Query Parameters): [https://developer.atlassian.com/cloud/confluence/rest/v2/api-group-page/#api-spaces-id-pages-get](https://developer.atlassian.com/cloud/confluence/rest/v2/api-group-page/#api-spaces-id-pages-get)

## Testing

```bash
composer test
```

## Credits

- [Joe Scott](https://github.com/jscott-rawnet)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
