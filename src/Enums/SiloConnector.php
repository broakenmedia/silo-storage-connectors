<?php

namespace Silo\StorageConnectors\Enums;

enum SiloConnector: string
{
    case GOOGLE_DRIVE = 'google_drive';
    case CONFLUENCE = 'confluence';
    case SLACK = 'slack';
}
