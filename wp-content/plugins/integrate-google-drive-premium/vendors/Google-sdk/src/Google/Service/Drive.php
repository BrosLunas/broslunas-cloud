<?php
/*
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * Service definition for Drive (v3).
 *
 * <p>
 * The API to interact with Drive.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/drive/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class IGDGoogle_Service_Drive extends IGDGoogle_Service
{
    /** View and manage the files in your Google Drive. */
    const DRIVE =
      'https://www.googleapis.com/auth/drive';
    /** View and manage its own configuration data in your Google Drive. */
    const DRIVE_APPDATA =
      'https://www.googleapis.com/auth/drive.appdata';
    /** View and manage Google Drive files and folders that you have opened or created with this app. */
    const DRIVE_FILE =
      'https://www.googleapis.com/auth/drive.file';
    /** View and manage metadata of files in your Google Drive. */
    const DRIVE_METADATA =
      'https://www.googleapis.com/auth/drive.metadata';
    /** View metadata for files in your Google Drive. */
    const DRIVE_METADATA_READONLY =
      'https://www.googleapis.com/auth/drive.metadata.readonly';
    /** View the photos, videos and albums in your Google Photos. */
    const DRIVE_PHOTOS_READONLY =
      'https://www.googleapis.com/auth/drive.photos.readonly';
    /** View the files in your Google Drive. */
    const DRIVE_READONLY =
      'https://www.googleapis.com/auth/drive.readonly';
    /** Modify your Google Apps Script scripts' behavior. */
    const DRIVE_SCRIPTS =
      'https://www.googleapis.com/auth/drive.scripts';

    public $about;
    public $changes;
    public $channels;
    public $comments;
    public $files;
    public $permissions;
    public $replies;
    public $revisions;
    public $drives;

    public $serviceName;
    
    /**
     * Constructs the internal representation of the Drive service.
     */
    public function __construct(IGDGoogle_Client $client)
    {
        parent::__construct($client);
        $this->rootUrl = 'https://www.googleapis.com/';
        $this->servicePath = 'drive/v3/';
        $this->version = 'v3';
        $this->serviceName = 'drive';

        $this->about = new IGDGoogle_Service_Drive_About_Resource(
            $this,
            $this->serviceName,
            'about',
            [
                'methods' => [
                    'get' => [
                        'path' => 'about',
                        'httpMethod' => 'GET',
                        'parameters' => [],
                    ],
                ],
            ]
        );
        $this->changes = new IGDGoogle_Service_Drive_Changes_Resource(
            $this,
            $this->serviceName,
            'changes',
            [
                'methods' => [
                    'getStartPageToken' => [
                        'path' => 'changes/startPageToken',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'driveId' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'changes',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeRemoved' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'includeItemsFromAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'restrictToMyDrive' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'spaces' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'driveId' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'watch' => [
                        'path' => 'changes/watch',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeRemoved' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'restrictToMyDrive' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'spaces' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->channels = new IGDGoogle_Service_Drive_Channels_Resource(
            $this,
            $this->serviceName,
            'channels',
            [
                'methods' => [
                    'stop' => [
                        'path' => 'channels/stop',
                        'httpMethod' => 'POST',
                        'parameters' => [],
                    ],
                ],
            ]
        );
        $this->comments = new IGDGoogle_Service_Drive_Comments_Resource(
            $this,
            $this->serviceName,
            'comments',
            [
                'methods' => [
                    'create' => [
                        'path' => 'files/{fileId}/comments',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'delete' => [
                        'path' => 'files/{fileId}/comments/{commentId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'files/{fileId}/comments/{commentId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeDeleted' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'files/{fileId}/comments',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeDeleted' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'startModifiedTime' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'files/{fileId}/comments/{commentId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->files = new IGDGoogle_Service_Drive_Files_Resource(
            $this,
            $this->serviceName,
            'files',
            [
                'methods' => [
                    'copy' => [
                        'path' => 'files/{fileId}/copy',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'ignoreDefaultVisibility' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'keepRevisionForever' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'ocrLanguage' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'create' => [
                        'path' => 'files',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'ignoreDefaultVisibility' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'keepRevisionForever' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'ocrLanguage' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'useContentAsIndexableText' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'delete' => [
                        'path' => 'files/{fileId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'emptyTrash' => [
                        'path' => 'files/trash',
                        'httpMethod' => 'DELETE',
                        'parameters' => [],
                    ], 'export' => [
                        'path' => 'files/{fileId}/export',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'mimeType' => [
                                'location' => 'query',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'generateIds' => [
                        'path' => 'files/generateIds',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'count' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'space' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'files/{fileId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'acknowledgeAbuse' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'files',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'corpora' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'corpus' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'includeItemsFromAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'orderBy' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'q' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'spaces' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'driveId' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'files/{fileId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'addParents' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'keepRevisionForever' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'ocrLanguage' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'removeParents' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'useContentAsIndexableText' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'watch' => [
                        'path' => 'files/{fileId}/watch',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'acknowledgeAbuse' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->permissions = new IGDGoogle_Service_Drive_Permissions_Resource(
            $this,
            $this->serviceName,
            'permissions',
            [
                'methods' => [
                    'create' => [
                        'path' => 'files/{fileId}/permissions',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'emailMessage' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'sendNotificationEmail' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'transferOwnership' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'delete' => [
                        'path' => 'files/{fileId}/permissions/{permissionId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'permissionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'files/{fileId}/permissions/{permissionId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'permissionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'files/{fileId}/permissions',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'files/{fileId}/permissions/{permissionId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'permissionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'transferOwnership' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'supportsAllDrives' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->replies = new IGDGoogle_Service_Drive_Replies_Resource(
            $this,
            $this->serviceName,
            'replies',
            [
                'methods' => [
                    'create' => [
                        'path' => 'files/{fileId}/comments/{commentId}/replies',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'delete' => [
                        'path' => 'files/{fileId}/comments/{commentId}/replies/{replyId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'replyId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'files/{fileId}/comments/{commentId}/replies/{replyId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'replyId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeDeleted' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'files/{fileId}/comments/{commentId}/replies',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'includeDeleted' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'files/{fileId}/comments/{commentId}/replies/{replyId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'commentId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'replyId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->revisions = new IGDGoogle_Service_Drive_Revisions_Resource(
            $this,
            $this->serviceName,
            'revisions',
            [
                'methods' => [
                    'delete' => [
                        'path' => 'files/{fileId}/revisions/{revisionId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'revisionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'files/{fileId}/revisions/{revisionId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'revisionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'acknowledgeAbuse' => [
                                'location' => 'query',
                                'type' => 'boolean',
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'files/{fileId}/revisions',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'files/{fileId}/revisions/{revisionId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'fileId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                            'revisionId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->drives = new IGDGoogle_Service_Drive_Drives_Resource(
            $this,
            $this->serviceName,
            'drives',
            [
                'methods' => [
                    'create' => [
                        'path' => 'drives',
                        'httpMethod' => 'POST',
                        'parameters' => [
                            'requestId' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'delete' => [
                        'path' => 'drives/{driveId}',
                        'httpMethod' => 'DELETE',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'get' => [
                        'path' => 'drives/{driveId}',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ], 'list' => [
                        'path' => 'drives',
                        'httpMethod' => 'GET',
                        'parameters' => [
                            'pageSize' => [
                                'location' => 'query',
                                'type' => 'integer',
                            ],
                            'pageToken' => [
                                'location' => 'query',
                                'type' => 'string',
                            ],
                        ],
                    ], 'update' => [
                        'path' => 'drives/{driveId}',
                        'httpMethod' => 'PATCH',
                        'parameters' => [
                            'driveId' => [
                                'location' => 'path',
                                'type' => 'string',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}

/**
 * The "about" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $about = $driveService->about;
 *  </code>.
 */
class IGDGoogle_Service_Drive_About_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Gets information about the user, the user's Drive, and system capabilities.
     * (about.get).
     *
     * @param array $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_About
     */
    public function get($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_About');
    }
}

/**
 * The "changes" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $changes = $driveService->changes;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Changes_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Gets the starting pageToken for listing future changes.
     * (changes.getStartPageToken).
     *
     * @param array $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_StartPageToken
     */
    public function getStartPageToken($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('getStartPageToken', [$params], 'IGDGoogle_Service_Drive_StartPageToken');
    }

    /**
     * Lists changes for a user. (changes.listChanges).
     *
     * @param string $pageToken The token for continuing a previous list request on
     *                          the next page. This should be set to the value of 'nextPageToken' from the
     *                          previous response or to the response from the getStartPageToken method.
     * @param array  $optParams optional parameters
     *
     * @opt_param bool includeRemoved Whether to include changes indicating that
     * items have left the view of the changes list, for example by deletion or lost
     * access.
     * @opt_param int pageSize The maximum number of changes to return per page.
     * @opt_param bool restrictToMyDrive Whether to restrict the results to changes
     * inside the My Drive hierarchy. This omits changes to files such as those in
     * the Application Data folder or shared files which have not been added to My
     * Drive.
     * @opt_param string spaces A comma-separated list of spaces to query within the
     * user corpus. Supported values are 'drive', 'appDataFolder' and 'photos'.
     *
     * @return IGDGoogle_Service_Drive_ChangeList
     */
    public function listChanges($pageToken, $optParams = [])
    {
        $params = ['pageToken' => $pageToken];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_ChangeList');
    }

    /**
     * Subscribes to changes for a user. (changes.watch).
     *
     * @param string            $pageToken The token for continuing a previous list request on
     *                                     the next page. This should be set to the value of 'nextPageToken' from the
     *                                     previous response or to the response from the getStartPageToken method.
     * @param IGDGoogle_Channel $postBody
     * @param array             $optParams optional parameters
     *
     * @opt_param bool includeRemoved Whether to include changes indicating that
     * items have left the view of the changes list, for example by deletion or lost
     * access.
     * @opt_param int pageSize The maximum number of changes to return per page.
     * @opt_param bool restrictToMyDrive Whether to restrict the results to changes
     * inside the My Drive hierarchy. This omits changes to files such as those in
     * the Application Data folder or shared files which have not been added to My
     * Drive.
     * @opt_param string spaces A comma-separated list of spaces to query within the
     * user corpus. Supported values are 'drive', 'appDataFolder' and 'photos'.
     *
     * @return IGDGoogle_Service_Drive_Channel
     */
    public function watch($pageToken, IGDGoogle_Service_Drive_Channel $postBody, $optParams = [])
    {
        $params = ['pageToken' => $pageToken, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('watch', [$params], 'IGDGoogle_Service_Drive_Channel');
    }
}

/**
 * The "channels" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $channels = $driveService->channels;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Channels_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Stop watching resources through this channel (channels.stop).
     *
     * @param IGDGoogle_Channel $postBody
     * @param array             $optParams optional parameters
     */
    public function stop(IGDGoogle_Service_Drive_Channel $postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('stop', [$params]);
    }
}

/**
 * The "comments" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $comments = $driveService->comments;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Comments_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Creates a new comment on a file. (comments.create).
     *
     * @param string            $fileId    the ID of the file
     * @param IGDGoogle_Comment $postBody
     * @param array             $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_Comment
     */
    public function create($fileId, IGDGoogle_Service_Drive_Comment $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('create', [$params], 'IGDGoogle_Service_Drive_Comment');
    }

    /**
     * Deletes a comment. (comments.delete).
     *
     * @param string $fileId    the ID of the file
     * @param string $commentId the ID of the comment
     * @param array  $optParams optional parameters
     */
    public function delete($fileId, $commentId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * Gets a comment by ID. (comments.get).
     *
     * @param string $fileId    the ID of the file
     * @param string $commentId the ID of the comment
     * @param array  $optParams optional parameters
     *
     * @opt_param bool includeDeleted Whether to return deleted comments. Deleted
     * comments will not include their original content.
     *
     * @return IGDGoogle_Service_Drive_Comment
     */
    public function get($fileId, $commentId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_Comment');
    }

    /**
     * Lists a file's comments. (comments.listComments).
     *
     * @param string $fileId    the ID of the file
     * @param array  $optParams optional parameters
     *
     * @opt_param bool includeDeleted Whether to include deleted comments. Deleted
     * comments will not include their original content.
     * @opt_param int pageSize The maximum number of comments to return per page.
     * @opt_param string pageToken The token for continuing a previous list request
     * on the next page. This should be set to the value of 'nextPageToken' from the
     * previous response.
     * @opt_param string startModifiedTime The minimum value of 'modifiedTime' for
     * the result comments (RFC 3339 date-time).
     *
     * @return IGDGoogle_Service_Drive_CommentList
     */
    public function listComments($fileId, $optParams = [])
    {
        $params = ['fileId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_CommentList');
    }

    /**
     * Updates a comment with patch semantics. (comments.update).
     *
     * @param string            $fileId    the ID of the file
     * @param string            $commentId the ID of the comment
     * @param IGDGoogle_Comment $postBody
     * @param array             $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_Comment
     */
    public function update($fileId, $commentId, IGDGoogle_Service_Drive_Comment $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_Comment');
    }
}

/**
 * The "files" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $files = $driveService->files;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Files_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Creates a copy of a file and applies any requested updates with patch
     * semantics. (files.copy).
     *
     * @param string              $fileId    the ID of the file
     * @param IGDGoogle_DriveFile $postBody
     * @param array               $optParams optional parameters
     *
     * @opt_param bool ignoreDefaultVisibility Whether to ignore the domain's
     * default visibility settings for the created file. Domain administrators can
     * choose to make all uploaded files visible to the domain by default; this
     * parameter bypasses that behavior for the request. Permissions are still
     * inherited from parent folders.
     * @opt_param bool keepRevisionForever Whether to set the 'keepForever' field in
     * the new head revision. This is only applicable to files with binary content
     * in Drive.
     * @opt_param string ocrLanguage A language hint for OCR processing during image
     * import (ISO 639-1 code).
     *
     * @return IGDGoogle_Http_Request|IGDGoogle_Service_Drive_DriveFile
     */
    public function copy($fileId, IGDGoogle_Service_Drive_DriveFile $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('copy', [$params], 'IGDGoogle_Service_Drive_DriveFile');
    }

    /**
     * Creates a new file. (files.create).
     *
     * @param IGDGoogle_DriveFile $postBody
     * @param array               $optParams optional parameters
     *
     * @opt_param bool ignoreDefaultVisibility Whether to ignore the domain's
     * default visibility settings for the created file. Domain administrators can
     * choose to make all uploaded files visible to the domain by default; this
     * parameter bypasses that behavior for the request. Permissions are still
     * inherited from parent folders.
     * @opt_param bool keepRevisionForever Whether to set the 'keepForever' field in
     * the new head revision. This is only applicable to files with binary content
     * in Drive.
     * @opt_param string ocrLanguage A language hint for OCR processing during image
     * import (ISO 639-1 code).
     * @opt_param bool useContentAsIndexableText Whether to use the uploaded content
     * as indexable text.
     *
     * @return IGDGoogle_Http_Request|IGDGoogle_Service_Drive_DriveFile
     */
    public function create(IGDGoogle_Service_Drive_DriveFile $postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('create', [$params], 'IGDGoogle_Service_Drive_DriveFile');
    }

    /**
     * Permanently deletes a file owned by the user without moving it to the trash.
     * If the target is a folder, all descendants owned by the user are also
     * deleted. (files.delete).
     *
     * @param string $fileId    the ID of the file
     * @param array  $optParams optional parameters
     */
    public function delete($fileId, $optParams = [])
    {
        $params = ['fileId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * Permanently deletes all of the user's trashed files. (files.emptyTrash).
     *
     * @param array $optParams optional parameters
     */
    public function emptyTrash($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('emptyTrash', [$params]);
    }

    /**
     * Exports a Google Doc to the requested MIME type and returns the exported
     * content. (files.export).
     *
     * @param string $fileId    the ID of the file
     * @param string $mimeType  the MIME type of the format requested for this
     *                          export
     * @param array  $optParams optional parameters
     */
    public function export($fileId, $mimeType, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'mimeType' => $mimeType];
        $params = array_merge($params, $optParams);

        return $this->call('export', [$params]);
    }

    /**
     * Generates a set of file IDs which can be provided in create requests.
     * (files.generateIds).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param int count The number of IDs to return.
     * @opt_param string space The space in which the IDs can be used to create new
     * files. Supported values are 'drive' and 'appDataFolder'.
     *
     * @return IGDGoogle_Service_Drive_GeneratedIds
     */
    public function generateIds($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('generateIds', [$params], 'IGDGoogle_Service_Drive_GeneratedIds');
    }

    /**
     * Gets a file's metadata or content by ID. (files.get).
     *
     * @param string $fileId    the ID of the file
     * @param array  $optParams optional parameters
     *
     * @opt_param bool acknowledgeAbuse Whether the user is acknowledging the risk
     * of downloading known malware or other abusive files. This is only applicable
     * when alt=media.
     *
     * @return IGDGoogle_Http_Request|IGDGoogle_Service_Drive_DriveFile
     */
    public function get($fileId, $optParams = [])
    {
        $params = ['fileId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_DriveFile');
    }

    /**
     * Lists or searches files. (files.listFiles).
     *
     * @param array $optParams optional parameters
     *
     * @opt_param string corpus The source of files to list.
     * @opt_param string orderBy A comma-separated list of sort keys. Valid keys are
     * 'createdTime', 'folder', 'modifiedByMeTime', 'modifiedTime', 'name',
     * 'quotaBytesUsed', 'recency', 'sharedWithMeTime', 'starred', and
     * 'viewedByMeTime'. Each key sorts ascending by default, but may be reversed
     * with the 'desc' modifier. Example usage: ?orderBy=folder,modifiedTime
     * desc,name. Please note that there is a current limitation for users with
     * approximately one million files in which the requested sort order is ignored.
     * @opt_param int pageSize The maximum number of files to return per page.
     * @opt_param string pageToken The token for continuing a previous list request
     * on the next page. This should be set to the value of 'nextPageToken' from the
     * previous response.
     * @opt_param string q A query for filtering the file results. See the "Search
     * for Files" guide for supported syntax.
     * @opt_param string spaces A comma-separated list of spaces to query within the
     * corpus. Supported values are 'drive', 'appDataFolder' and 'photos'.
     *
     * @return IGDGoogle_Http_Request|IGDGoogle_Service_Drive_FileList
     */
    public function listFiles($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_FileList');
    }

    /**
     * Updates a file's metadata and/or content with patch semantics. (files.update).
     *
     * @param string              $fileId    the ID of the file
     * @param IGDGoogle_DriveFile $postBody
     * @param array               $optParams optional parameters
     *
     * @opt_param string addParents A comma-separated list of parent IDs to add.
     * @opt_param bool keepRevisionForever Whether to set the 'keepForever' field in
     * the new head revision. This is only applicable to files with binary content
     * in Drive.
     * @opt_param string ocrLanguage A language hint for OCR processing during image
     * import (ISO 639-1 code).
     * @opt_param string removeParents A comma-separated list of parent IDs to
     * remove.
     * @opt_param bool useContentAsIndexableText Whether to use the uploaded content
     * as indexable text.
     *
     * @return IGDGoogle_Http_Request|IGDGoogle_Service_Drive_DriveFile
     */
    public function update($fileId, IGDGoogle_Service_Drive_DriveFile $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_DriveFile');
    }

    /**
     * Subscribes to changes to a file (files.watch).
     *
     * @param string            $fileId    the ID of the file
     * @param IGDGoogle_Channel $postBody
     * @param array             $optParams optional parameters
     *
     * @opt_param bool acknowledgeAbuse Whether the user is acknowledging the risk
     * of downloading known malware or other abusive files. This is only applicable
     * when alt=media.
     *
     * @return IGDGoogle_Service_Drive_Channel
     */
    public function watch($fileId, IGDGoogle_Service_Drive_Channel $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('watch', [$params], 'IGDGoogle_Service_Drive_Channel');
    }
}

/**
 * The "permissions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $permissions = $driveService->permissions;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Permissions_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Creates a permission for a file. (permissions.create).
     *
     * @param string               $fileId    the ID of the file
     * @param IGDGoogle_Permission $postBody
     * @param array                $optParams optional parameters
     *
     * @opt_param string emailMessage A custom message to include in the
     * notification email.
     * @opt_param bool sendNotificationEmail Whether to send a notification email
     * when sharing to users or groups. This defaults to true for users and groups,
     * and is not allowed for other requests. It must not be disabled for ownership
     * transfers.
     * @opt_param bool transferOwnership Whether to transfer ownership to the
     * specified user and downgrade the current owner to a writer. This parameter is
     * required as an acknowledgement of the side effect.
     *
     * @return IGDGoogle_Service_Drive_Permission
     */
    public function create($fileId, IGDGoogle_Service_Drive_Permission $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('create', [$params], 'IGDGoogle_Service_Drive_Permission');
    }

    /**
     * Deletes a permission. (permissions.delete).
     *
     * @param string $fileId       the ID of the file
     * @param string $permissionId the ID of the permission
     * @param array  $optParams    optional parameters
     */
    public function delete($fileId, $permissionId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'permissionId' => $permissionId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * Gets a permission by ID. (permissions.get).
     *
     * @param string $fileId       the ID of the file
     * @param string $permissionId the ID of the permission
     * @param array  $optParams    optional parameters
     *
     * @return IGDGoogle_Service_Drive_Permission
     */
    public function get($fileId, $permissionId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'permissionId' => $permissionId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_Permission');
    }

    /**
     * Lists a file's permissions. (permissions.listPermissions).
     *
     * @param string $fileId    the ID of the file
     * @param array  $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_PermissionList
     */
    public function listPermissions($fileId, $optParams = [])
    {
        $params = ['fileId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_PermissionList');
    }

    /**
     * Updates a permission with patch semantics. (permissions.update).
     *
     * @param string               $fileId       the ID of the file
     * @param string               $permissionId the ID of the permission
     * @param IGDGoogle_Permission $postBody
     * @param array                $optParams    optional parameters
     *
     * @opt_param bool transferOwnership Whether to transfer ownership to the
     * specified user and downgrade the current owner to a writer. This parameter is
     * required as an acknowledgement of the side effect.
     *
     * @return IGDGoogle_Service_Drive_Permission
     */
    public function update($fileId, $permissionId, IGDGoogle_Service_Drive_Permission $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'permissionId' => $permissionId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_Permission');
    }
}

/**
 * The "replies" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $replies = $driveService->replies;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Replies_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Creates a new reply to a comment. (replies.create).
     *
     * @param string          $fileId    the ID of the file
     * @param string          $commentId the ID of the comment
     * @param IGDGoogle_Reply $postBody
     * @param array           $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_Reply
     */
    public function create($fileId, $commentId, IGDGoogle_Service_Drive_Reply $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('create', [$params], 'IGDGoogle_Service_Drive_Reply');
    }

    /**
     * Deletes a reply. (replies.delete).
     *
     * @param string $fileId    the ID of the file
     * @param string $commentId the ID of the comment
     * @param string $replyId   the ID of the reply
     * @param array  $optParams optional parameters
     */
    public function delete($fileId, $commentId, $replyId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId, 'replyId' => $replyId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * Gets a reply by ID. (replies.get).
     *
     * @param string $fileId    the ID of the file
     * @param string $commentId the ID of the comment
     * @param string $replyId   the ID of the reply
     * @param array  $optParams optional parameters
     *
     * @opt_param bool includeDeleted Whether to return deleted replies. Deleted
     * replies will not include their original content.
     *
     * @return IGDGoogle_Service_Drive_Reply
     */
    public function get($fileId, $commentId, $replyId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId, 'replyId' => $replyId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_Reply');
    }

    /**
     * Lists a comment's replies. (replies.listReplies).
     *
     * @param string $fileId    the ID of the file
     * @param string $commentId the ID of the comment
     * @param array  $optParams optional parameters
     *
     * @opt_param bool includeDeleted Whether to include deleted replies. Deleted
     * replies will not include their original content.
     * @opt_param int pageSize The maximum number of replies to return per page.
     * @opt_param string pageToken The token for continuing a previous list request
     * on the next page. This should be set to the value of 'nextPageToken' from the
     * previous response.
     *
     * @return IGDGoogle_Service_Drive_ReplyList
     */
    public function listReplies($fileId, $commentId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_ReplyList');
    }

    /**
     * Updates a reply with patch semantics. (replies.update).
     *
     * @param string          $fileId    the ID of the file
     * @param string          $commentId the ID of the comment
     * @param string          $replyId   the ID of the reply
     * @param IGDGoogle_Reply $postBody
     * @param array           $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_Reply
     */
    public function update($fileId, $commentId, $replyId, IGDGoogle_Service_Drive_Reply $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'commentId' => $commentId, 'replyId' => $replyId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_Reply');
    }
}

/**
 * The "revisions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new IGDGoogle_Service_Drive(...);
 *   $revisions = $driveService->revisions;
 *  </code>.
 */
class IGDGoogle_Service_Drive_Revisions_Resource extends IGDGoogle_Service_Resource
{
    /**
     * Permanently deletes a revision. This method is only applicable to files with
     * binary content in Drive. (revisions.delete).
     *
     * @param string $fileId     the ID of the file
     * @param string $revisionId the ID of the revision
     * @param array  $optParams  optional parameters
     */
    public function delete($fileId, $revisionId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'revisionId' => $revisionId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * Gets a revision's metadata or content by ID. (revisions.get).
     *
     * @param string $fileId     the ID of the file
     * @param string $revisionId the ID of the revision
     * @param array  $optParams  optional parameters
     *
     * @opt_param bool acknowledgeAbuse Whether the user is acknowledging the risk
     * of downloading known malware or other abusive files. This is only applicable
     * when alt=media.
     *
     * @return IGDGoogle_Service_Drive_Revision
     */
    public function get($fileId, $revisionId, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'revisionId' => $revisionId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_Revision');
    }

    /**
     * Lists a file's revisions. (revisions.listRevisions).
     *
     * @param string $fileId    the ID of the file
     * @param array  $optParams optional parameters
     *
     * @return IGDGoogle_Service_Drive_RevisionList
     */
    public function listRevisions($fileId, $optParams = [])
    {
        $params = ['fileId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_RevisionList');
    }

    /**
     * Updates a revision with patch semantics. (revisions.update).
     *
     * @param string             $fileId     the ID of the file
     * @param string             $revisionId the ID of the revision
     * @param IGDGoogle_Revision $postBody
     * @param array              $optParams  optional parameters
     *
     * @return IGDGoogle_Service_Drive_Revision
     */
    public function update($fileId, $revisionId, IGDGoogle_Service_Drive_Revision $postBody, $optParams = [])
    {
        $params = ['fileId' => $fileId, 'revisionId' => $revisionId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_Revision');
    }
}

class IGDGoogle_Service_Drive_Drives_Resource extends IGDGoogle_Service_Resource
{
    /**
     * @param array $optParams
     *
     * @return IGDGoogle_Service_Drive_Drive
     */
    public function create(IGDGoogle_Service_Drive_Drive $postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('create', [$params], 'IGDGoogle_Service_Drive_Drive');
    }

    /**
     * @param string $fileId
     * @param array  $optParams
     */
    public function delete($fileId, $optParams = [])
    {
        $params = ['driveId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('delete', [$params]);
    }

    /**
     * @param string $fileId
     * @param array  $optParams
     *
     * @return IGDGoogle_Service_Drive_DriveList
     */
    public function get($fileId, $optParams = [])
    {
        $params = ['driveId' => $fileId];
        $params = array_merge($params, $optParams);

        return $this->call('get', [$params], 'IGDGoogle_Service_Drive_DriveList');
    }

    /**
     * @param array $optParams
     *
     * @return IGDGoogle_Service_Drive_DriveList
     */
    public function listDrives($optParams = [])
    {
        $params = [];
        $params = array_merge($params, $optParams);

        return $this->call('list', [$params], 'IGDGoogle_Service_Drive_DriveList');
    }

    /**
     * @param string $fileId
     * @param array  $optParams
     *
     * @return IGDGoogle_Service_Drive_DriveList
     */
    public function update($fileId, IGDGoogle_Service_Drive_Drive $postBody, $optParams = [])
    {
        $params = ['driveId' => $fileId, 'postBody' => $postBody];
        $params = array_merge($params, $optParams);

        return $this->call('update', [$params], 'IGDGoogle_Service_Drive_DriveList');
    }
}

class IGDGoogle_Service_Drive_About extends IGDGoogle_Collection
{
    public $appInstalled;
    public $exportFormats;
    public $folderColorPalette;
    public $importFormats;
    public $kind;
    public $maxImportSizes;
    public $maxUploadSize;
    protected $collection_key = 'folderColorPalette';
    protected $internal_gapi_mappings = [
    ];
    protected $storageQuotaType = 'IGDGoogle_Service_Drive_AboutStorageQuota';
    protected $storageQuotaDataType = '';
    protected $userType = 'IGDGoogle_Service_Drive_User';
    protected $userDataType = '';

    public function setAppInstalled($appInstalled)
    {
        $this->appInstalled = $appInstalled;
    }

    public function getAppInstalled()
    {
        return $this->appInstalled;
    }

    public function setExportFormats($exportFormats)
    {
        $this->exportFormats = $exportFormats;
    }

    public function getExportFormats()
    {
        return $this->exportFormats;
    }

    public function setFolderColorPalette($folderColorPalette)
    {
        $this->folderColorPalette = $folderColorPalette;
    }

    public function getFolderColorPalette()
    {
        return $this->folderColorPalette;
    }

    public function setImportFormats($importFormats)
    {
        $this->importFormats = $importFormats;
    }

    public function getImportFormats()
    {
        return $this->importFormats;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setMaxImportSizes($maxImportSizes)
    {
        $this->maxImportSizes = $maxImportSizes;
    }

    public function getMaxImportSizes()
    {
        return $this->maxImportSizes;
    }

    public function setMaxUploadSize($maxUploadSize)
    {
        $this->maxUploadSize = $maxUploadSize;
    }

    public function getMaxUploadSize()
    {
        return $this->maxUploadSize;
    }

    public function setStorageQuota(IGDGoogle_Service_Drive_AboutStorageQuota $storageQuota)
    {
        $this->storageQuota = $storageQuota;
    }

    public function getStorageQuota()
    {
        return $this->storageQuota;
    }

    public function setUser(IGDGoogle_Service_Drive_User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}

class IGDGoogle_Service_Drive_AboutStorageQuota extends IGDGoogle_Model
{
    public $limit;
    public $usage;
    public $usageInDrive;
    public $usageInDriveTrash;
    protected $internal_gapi_mappings = [
    ];

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setUsage($usage)
    {
        $this->usage = $usage;
    }

    public function getUsage()
    {
        return $this->usage;
    }

    public function setUsageInDrive($usageInDrive)
    {
        $this->usageInDrive = $usageInDrive;
    }

    public function getUsageInDrive()
    {
        return $this->usageInDrive;
    }

    public function setUsageInDriveTrash($usageInDriveTrash)
    {
        $this->usageInDriveTrash = $usageInDriveTrash;
    }

    public function getUsageInDriveTrash()
    {
        return $this->usageInDriveTrash;
    }
}

class IGDGoogle_Service_Drive_Change extends IGDGoogle_Model
{
    public $fileId;
    public $kind;
    public $removed;
    public $time;
    public $changeType;
    public $driveId;
    public $drive;
    protected $internal_gapi_mappings = [
    ];
    protected $fileType = 'IGDGoogle_Service_Drive_DriveFile';
    protected $fileDataType = '';

    public function getChangeType()
    {
        return $this->changeType;
    }

    public function getDriveId()
    {
        return $this->driveId;
    }

    public function setChangeType($changeType)
    {
        $this->changeType = $changeType;
    }

    public function setDriveId($driveId)
    {
        $this->driveId = $driveId;
    }

    public function setFile(IGDGoogle_Service_Drive_DriveFile $file)
    {
        $this->file = $file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
    }

    public function getFileId()
    {
        return $this->fileId;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setRemoved($removed)
    {
        $this->removed = $removed;
    }

    public function getRemoved()
    {
        return $this->removed;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setDrive(IGDGoogle_Service_Drive_Drive $drive)
    {
        $this->drive = $drive;
    }

    public function getDrive()
    {
        return $this->drive;
    }
}

class IGDGoogle_Service_Drive_ChangeList extends IGDGoogle_Collection
{
    public $kind;
    public $newStartPageToken;
    public $nextPageToken;
    protected $collection_key = 'changes';
    protected $internal_gapi_mappings = [
    ];
    protected $changesType = 'IGDGoogle_Service_Drive_Change';
    protected $changesDataType = 'array';

    public function setChanges($changes)
    {
        $this->changes = $changes;
    }

    public function getChanges()
    {
        return $this->changes;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setNewStartPageToken($newStartPageToken)
    {
        $this->newStartPageToken = $newStartPageToken;
    }

    public function getNewStartPageToken()
    {
        return $this->newStartPageToken;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }
}

class IGDGoogle_Service_Drive_Channel extends IGDGoogle_Model
{
    public $address;
    public $expiration;
    public $id;
    public $kind;
    public $params;
    public $payload;
    public $resourceId;
    public $resourceUri;
    public $token;
    public $type;
    protected $internal_gapi_mappings = [
    ];

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    public function getExpiration()
    {
        return $this->expiration;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function setResourceUri($resourceUri)
    {
        $this->resourceUri = $resourceUri;
    }

    public function getResourceUri()
    {
        return $this->resourceUri;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}

class IGDGoogle_Service_Drive_Comment extends IGDGoogle_Collection
{
    public $anchor;
    public $content;
    public $createdTime;
    public $deleted;
    public $htmlContent;
    public $id;
    public $kind;
    public $modifiedTime;
    public $resolved;
    protected $collection_key = 'replies';
    protected $internal_gapi_mappings = [
    ];
    protected $authorType = 'IGDGoogle_Service_Drive_User';
    protected $authorDataType = '';
    protected $quotedFileContentType = 'IGDGoogle_Service_Drive_CommentQuotedFileContent';
    protected $quotedFileContentDataType = '';
    protected $repliesType = 'IGDGoogle_Service_Drive_Reply';
    protected $repliesDataType = 'array';

    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    }

    public function getAnchor()
    {
        return $this->anchor;
    }

    public function setAuthor(IGDGoogle_Service_Drive_User $author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;
    }

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    public function setQuotedFileContent(IGDGoogle_Service_Drive_CommentQuotedFileContent $quotedFileContent)
    {
        $this->quotedFileContent = $quotedFileContent;
    }

    public function getQuotedFileContent()
    {
        return $this->quotedFileContent;
    }

    public function setReplies($replies)
    {
        $this->replies = $replies;
    }

    public function getReplies()
    {
        return $this->replies;
    }

    public function setResolved($resolved)
    {
        $this->resolved = $resolved;
    }

    public function getResolved()
    {
        return $this->resolved;
    }
}

class IGDGoogle_Service_Drive_CommentList extends IGDGoogle_Collection
{
    public $kind;
    public $nextPageToken;
    protected $collection_key = 'comments';
    protected $internal_gapi_mappings = [
    ];
    protected $commentsType = 'IGDGoogle_Service_Drive_Comment';
    protected $commentsDataType = 'array';

    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }
}

class IGDGoogle_Service_Drive_CommentQuotedFileContent extends IGDGoogle_Model
{
    public $mimeType;
    public $value;
    protected $internal_gapi_mappings = [
    ];

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

class IGDGoogle_Service_Drive_DriveFile extends IGDGoogle_Collection
{
    public $appProperties;
    public $createdTime;
    public $description;
    public $explicitlyTrashed;
    public $fileExtension;
    public $folderColorRgb;
    public $fullFileExtension;
    public $headRevisionId;
    public $iconLink;
    public $id;
    public $kind;
    public $md5Checksum;
    public $mimeType;
    public $modifiedByMeTime;
    public $modifiedTime;
    public $name;
    public $originalFilename;
    public $ownedByMe;
    public $parents;
    public $properties;
    public $quotaBytesUsed;
    public $shared;
    public $sharedWithMeTime;
    public $size;
    public $spaces;
    public $starred;
    public $thumbnailLink;
    public $trashed;
    public $driveId;
    public $version;
    public $viewedByMe;
    public $viewedByMeTime;
    public $copyRequiresWriterPermission;
    public $webContentLink;
    public $webViewLink;
    public $exportLinks;
    public $resourceKey;
    public $writersCanShare;
    protected $collection_key = 'spaces';
    protected $internal_gapi_mappings = [
    ];
    protected $capabilitiesType = 'IGDGoogle_Service_Drive_DriveFileCapabilities';
    protected $capabilitiesDataType = '';
    protected $contentHintsType = 'IGDGoogle_Service_Drive_DriveFileContentHints';
    protected $contentHintsDataType = '';
    protected $imageMediaMetadataType = 'IGDGoogle_Service_Drive_DriveFileImageMediaMetadata';
    protected $imageMediaMetadataDataType = '';
    protected $lastModifyingUserType = 'IGDGoogle_Service_Drive_User';
    protected $lastModifyingUserDataType = '';
    protected $ownersType = 'IGDGoogle_Service_Drive_User';
    protected $ownersDataType = 'array';
    protected $permissionsType = 'IGDGoogle_Service_Drive_Permission';
    protected $permissionsDataType = 'array';
    protected $sharingUserType = 'IGDGoogle_Service_Drive_User';
    protected $sharingUserDataType = '';
    protected $videoMediaMetadataType = 'IGDGoogle_Service_Drive_DriveFileVideoMediaMetadata';
    protected $videoMediaMetadataDataType = '';
    protected $shortcutDetailsType = 'IGDGoogle_Service_Drive_DriveFileShortcutDetails';
    protected $shortcutDetailsDataType = '';

    public function setAppProperties($appProperties)
    {
        $this->appProperties = $appProperties;
    }

    public function getAppProperties()
    {
        return $this->appProperties;
    }

    public function setCapabilities(IGDGoogle_Service_Drive_DriveFileCapabilities $capabilities)
    {
        $this->capabilities = $capabilities;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function setContentHints(IGDGoogle_Service_Drive_DriveFileContentHints $contentHints)
    {
        $this->contentHints = $contentHints;
    }

    public function getContentHints()
    {
        return $this->contentHints;
    }

    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;
    }

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setExplicitlyTrashed($explicitlyTrashed)
    {
        $this->explicitlyTrashed = $explicitlyTrashed;
    }

    public function getExplicitlyTrashed()
    {
        return $this->explicitlyTrashed;
    }

    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    public function setFolderColorRgb($folderColorRgb)
    {
        $this->folderColorRgb = $folderColorRgb;
    }

    public function getFolderColorRgb()
    {
        return $this->folderColorRgb;
    }

    public function setFullFileExtension($fullFileExtension)
    {
        $this->fullFileExtension = $fullFileExtension;
    }

    public function getFullFileExtension()
    {
        return $this->fullFileExtension;
    }

    public function setHeadRevisionId($headRevisionId)
    {
        $this->headRevisionId = $headRevisionId;
    }

    public function getHeadRevisionId()
    {
        return $this->headRevisionId;
    }

    public function setIconLink($iconLink)
    {
        $this->iconLink = $iconLink;
    }

    public function getIconLink()
    {
        return $this->iconLink;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setImageMediaMetadata(IGDGoogle_Service_Drive_DriveFileImageMediaMetadata $imageMediaMetadata)
    {
        $this->imageMediaMetadata = $imageMediaMetadata;
    }

    public function getImageMediaMetadata()
    {
        return $this->imageMediaMetadata;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setLastModifyingUser(IGDGoogle_Service_Drive_User $lastModifyingUser)
    {
        $this->lastModifyingUser = $lastModifyingUser;
    }

    public function getLastModifyingUser()
    {
        return $this->lastModifyingUser;
    }

    public function setMd5Checksum($md5Checksum)
    {
        $this->md5Checksum = $md5Checksum;
    }

    public function getMd5Checksum()
    {
        return $this->md5Checksum;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setModifiedByMeTime($modifiedByMeTime)
    {
        $this->modifiedByMeTime = $modifiedByMeTime;
    }

    public function getModifiedByMeTime()
    {
        return $this->modifiedByMeTime;
    }

    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
    }

    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    public function setOwnedByMe($ownedByMe)
    {
        $this->ownedByMe = $ownedByMe;
    }

    public function getOwnedByMe()
    {
        return $this->ownedByMe;
    }

    public function setOwners($owners)
    {
        $this->owners = $owners;
    }

    public function getOwners()
    {
        return $this->owners;
    }

    public function setParents($parents)
    {
        $this->parents = $parents;
    }

    public function getParents()
    {
        return $this->parents;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setProperties($properties)
    {
        $this->properties = $properties;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setQuotaBytesUsed($quotaBytesUsed)
    {
        $this->quotaBytesUsed = $quotaBytesUsed;
    }

    public function getQuotaBytesUsed()
    {
        return $this->quotaBytesUsed;
    }

    public function setShared($shared)
    {
        $this->shared = $shared;
    }

    public function getShared()
    {
        return $this->shared;
    }

    public function setSharedWithMeTime($sharedWithMeTime)
    {
        $this->sharedWithMeTime = $sharedWithMeTime;
    }

    public function getSharedWithMeTime()
    {
        return $this->sharedWithMeTime;
    }

    public function setSharingUser(IGDGoogle_Service_Drive_User $sharingUser)
    {
        $this->sharingUser = $sharingUser;
    }

    public function getSharingUser()
    {
        return $this->sharingUser;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSpaces($spaces)
    {
        $this->spaces = $spaces;
    }

    public function getSpaces()
    {
        return $this->spaces;
    }

    public function setStarred($starred)
    {
        $this->starred = $starred;
    }

    public function getStarred()
    {
        return $this->starred;
    }

    public function setThumbnailLink($thumbnailLink)
    {
        $this->thumbnailLink = $thumbnailLink;
    }

    public function getThumbnailLink()
    {
        return $this->thumbnailLink;
    }

    public function setTrashed($trashed)
    {
        $this->trashed = $trashed;
    }

    public function getTrashed()
    {
        return $this->trashed;
    }

    public function setDriveId($driveId)
    {
        $this->driveId = $driveId;
    }

    public function getDriveId()
    {
        return $this->driveId;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVideoMediaMetadata(IGDGoogle_Service_Drive_DriveFileVideoMediaMetadata $videoMediaMetadata)
    {
        $this->videoMediaMetadata = $videoMediaMetadata;
    }

    public function getVideoMediaMetadata()
    {
        return $this->videoMediaMetadata;
    }

    public function setViewedByMe($viewedByMe)
    {
        $this->viewedByMe = $viewedByMe;
    }

    public function getViewedByMe()
    {
        return $this->viewedByMe;
    }

    public function setViewedByMeTime($viewedByMeTime)
    {
        $this->viewedByMeTime = $viewedByMeTime;
    }

    public function getViewedByMeTime()
    {
        return $this->viewedByMeTime;
    }

    public function setCopyRequiresWriterPermission($copyRequiresWriterPermission)
    {
        $this->copyRequiresWriterPermission = $copyRequiresWriterPermission;
    }

    public function getCopyRequiresWriterPermission()
    {
        return $this->copyRequiresWriterPermission;
    }

    public function setWebContentLink($webContentLink)
    {
        $this->webContentLink = $webContentLink;
    }

    public function getWebContentLink()
    {
        return $this->webContentLink;
    }

    public function setWebViewLink($webViewLink)
    {
        $this->webViewLink = $webViewLink;
    }

    public function getWebViewLink()
    {
        return $this->webViewLink;
    }

    public function setExportLinks($exportLinks)
    {
        $this->exportLinks = $exportLinks;
    }

    public function getExportLinks()
    {
        return $this->exportLinks;
    }

    public function setResourceKey($resourceKey)
    {
        $this->resourceKey = $resourceKey;
    }

    public function getResourceKey()
    {
        return $this->resourceKey;
    }

    public function setWritersCanShare($writersCanShare)
    {
        $this->writersCanShare = $writersCanShare;
    }

    public function getWritersCanShare()
    {
        return $this->writersCanShare;
    }

    public function setShortcutDetails(IGDGoogle_Service_Drive_DriveFileShortcutDetails $shortcutDetails)
    {
        $this->shortcutDetails = $shortcutDetails;
    }

    public function getShortcutDetails()
    {
        return $this->shortcutDetails;
    }
}

class IGDGoogle_Service_Drive_DriveFileCapabilities extends IGDGoogle_Model
{
    public $canAddChildren;
    public $canComment;
    public $canCopy;
    public $canDelete;
    public $canDownload;
    public $canEdit;
    public $canListChildren;
    public $canMoveItemOutOfDrive;
    public $canMoveItemWithinDrive;
    public $canReadRevisions;
    public $canRemoveChildren;
    public $canRename;
    public $canShare;
    public $canTrash;
    public $canUntrash;
    public $canChangeCopyRequiresWriterPermission;
    protected $internal_gapi_mappings = [
    ];

    public function getCanDelete()
    {
        return $this->canDelete;
    }

    public function getCanDownload()
    {
        return $this->canDownload;
    }

    public function getCanListChildren()
    {
        return $this->canListChildren;
    }

    public function getCanMoveItemOutOfDrive()
    {
        return $this->canMoveItemOutOfDrive;
    }

    public function getCanMoveItemWithinDrive()
    {
        return $this->canMoveItemWithinDrive;
    }

    public function getCanReadRevisions()
    {
        return $this->canReadRevisions;
    }

    public function getCanRemoveChildren()
    {
        return $this->canRemoveChildren;
    }

    public function getCanRename()
    {
        return $this->canRename;
    }

    public function getCanTrash()
    {
        return $this->canTrash;
    }

    public function getCanUntrash()
    {
        return $this->canUntrash;
    }

    public function getCanChangeCopyRequiresWriterPermission()
    {
        return $this->canChangeCopyRequiresWriterPermission;
    }

    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
    }

    public function setCanDownload($canDownload)
    {
        $this->canDownload = $canDownload;
    }

    public function setCanListChildren($canListChildren)
    {
        $this->canListChildren = $canListChildren;
    }

    public function setCanMoveItemOutOfDrive($canMoveItemOutOfDrive)
    {
        $this->canMoveItemOutOfDrive = $canMoveItemOutOfDrive;
    }

    public function setCanMoveItemWithinDrive($canMoveItemWithinDrive)
    {
        $this->canMoveItemWithinDrive = $canMoveItemWithinDrive;
    }

    public function setCanReadRevisions($canReadRevisions)
    {
        $this->canReadRevisions = $canReadRevisions;
    }

    public function setCanRemoveChildren($canRemoveChildren)
    {
        $this->canRemoveChildren = $canRemoveChildren;
    }

    public function setCanRename($canRename)
    {
        $this->canRename = $canRename;
    }

    public function setCanTrash($canTrash)
    {
        $this->canTrash = $canTrash;
    }

    public function setCanUntrash($canUntrash)
    {
        $this->canUntrash = $canUntrash;
    }

    public function setCanChangeCopyRequiresWriterPermission($canChangeCopyRequiresWriterPermission)
    {
        $this->canChangeCopyRequiresWriterPermission = $canChangeCopyRequiresWriterPermission;
    }

    public function setCanComment($canComment)
    {
        $this->canComment = $canComment;
    }

    public function getCanComment()
    {
        return $this->canComment;
    }

    public function setCanCopy($canCopy)
    {
        $this->canCopy = $canCopy;
    }

    public function getCanCopy()
    {
        return $this->canCopy;
    }

    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }

    public function getCanEdit()
    {
        return $this->canEdit;
    }

    public function setCanShare($canShare)
    {
        $this->canShare = $canShare;
    }

    public function getCanShare()
    {
        return $this->canShare;
    }
}

class IGDGoogle_Service_Drive_DriveFileContentHints extends IGDGoogle_Model
{
    public $indexableText;
    protected $internal_gapi_mappings = [
    ];
    protected $thumbnailType = 'IGDGoogle_Service_Drive_DriveFileContentHintsThumbnail';
    protected $thumbnailDataType = '';

    public function setIndexableText($indexableText)
    {
        $this->indexableText = $indexableText;
    }

    public function getIndexableText()
    {
        return $this->indexableText;
    }

    public function setThumbnail(IGDGoogle_Service_Drive_DriveFileContentHintsThumbnail $thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }
}

class IGDGoogle_Service_Drive_DriveFileContentHintsThumbnail extends IGDGoogle_Model
{
    public $image;
    public $mimeType;
    protected $internal_gapi_mappings = [
    ];

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }
}

class IGDGoogle_Service_Drive_DriveFileImageMediaMetadata extends IGDGoogle_Model
{
    public $aperture;
    public $cameraMake;
    public $cameraModel;
    public $colorSpace;
    public $exposureBias;
    public $exposureMode;
    public $exposureTime;
    public $flashUsed;
    public $focalLength;
    public $height;
    public $isoSpeed;
    public $lens;
    public $maxApertureValue;
    public $meteringMode;
    public $rotation;
    public $sensor;
    public $subjectDistance;
    public $time;
    public $whiteBalance;
    public $width;
    protected $internal_gapi_mappings = [
    ];
    protected $locationType = 'IGDGoogle_Service_Drive_DriveFileImageMediaMetadataLocation';
    protected $locationDataType = '';

    public function setAperture($aperture)
    {
        $this->aperture = $aperture;
    }

    public function getAperture()
    {
        return $this->aperture;
    }

    public function setCameraMake($cameraMake)
    {
        $this->cameraMake = $cameraMake;
    }

    public function getCameraMake()
    {
        return $this->cameraMake;
    }

    public function setCameraModel($cameraModel)
    {
        $this->cameraModel = $cameraModel;
    }

    public function getCameraModel()
    {
        return $this->cameraModel;
    }

    public function setColorSpace($colorSpace)
    {
        $this->colorSpace = $colorSpace;
    }

    public function getColorSpace()
    {
        return $this->colorSpace;
    }

    public function setExposureBias($exposureBias)
    {
        $this->exposureBias = $exposureBias;
    }

    public function getExposureBias()
    {
        return $this->exposureBias;
    }

    public function setExposureMode($exposureMode)
    {
        $this->exposureMode = $exposureMode;
    }

    public function getExposureMode()
    {
        return $this->exposureMode;
    }

    public function setExposureTime($exposureTime)
    {
        $this->exposureTime = $exposureTime;
    }

    public function getExposureTime()
    {
        return $this->exposureTime;
    }

    public function setFlashUsed($flashUsed)
    {
        $this->flashUsed = $flashUsed;
    }

    public function getFlashUsed()
    {
        return $this->flashUsed;
    }

    public function setFocalLength($focalLength)
    {
        $this->focalLength = $focalLength;
    }

    public function getFocalLength()
    {
        return $this->focalLength;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setIsoSpeed($isoSpeed)
    {
        $this->isoSpeed = $isoSpeed;
    }

    public function getIsoSpeed()
    {
        return $this->isoSpeed;
    }

    public function setLens($lens)
    {
        $this->lens = $lens;
    }

    public function getLens()
    {
        return $this->lens;
    }

    public function setLocation(IGDGoogle_Service_Drive_DriveFileImageMediaMetadataLocation $location)
    {
        $this->location = $location;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setMaxApertureValue($maxApertureValue)
    {
        $this->maxApertureValue = $maxApertureValue;
    }

    public function getMaxApertureValue()
    {
        return $this->maxApertureValue;
    }

    public function setMeteringMode($meteringMode)
    {
        $this->meteringMode = $meteringMode;
    }

    public function getMeteringMode()
    {
        return $this->meteringMode;
    }

    public function setRotation($rotation)
    {
        $this->rotation = $rotation;
    }

    public function getRotation()
    {
        return $this->rotation;
    }

    public function setSensor($sensor)
    {
        $this->sensor = $sensor;
    }

    public function getSensor()
    {
        return $this->sensor;
    }

    public function setSubjectDistance($subjectDistance)
    {
        $this->subjectDistance = $subjectDistance;
    }

    public function getSubjectDistance()
    {
        return $this->subjectDistance;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setWhiteBalance($whiteBalance)
    {
        $this->whiteBalance = $whiteBalance;
    }

    public function getWhiteBalance()
    {
        return $this->whiteBalance;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }
}

class IGDGoogle_Service_Drive_DriveFileImageMediaMetadataLocation extends IGDGoogle_Model
{
    public $altitude;
    public $latitude;
    public $longitude;
    protected $internal_gapi_mappings = [
    ];

    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
    }

    public function getAltitude()
    {
        return $this->altitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }
}

class IGDGoogle_Service_Drive_DriveFileVideoMediaMetadata extends IGDGoogle_Model
{
    public $durationMillis;
    public $height;
    public $width;
    protected $internal_gapi_mappings = [
    ];

    public function setDurationMillis($durationMillis)
    {
        $this->durationMillis = $durationMillis;
    }

    public function getDurationMillis()
    {
        return $this->durationMillis;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }
}

class IGDGoogle_Service_Drive_DriveFileShortcutDetails extends IGDGoogle_Model
{
    public $targetId;
    public $targetMimeType;
    public $targetResourceKey;
    protected $internal_gapi_mappings = [
    ];

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setTargetMimeType($targetMimeType)
    {
        $this->targetMimeType = $targetMimeType;
    }

    public function getTargetMimeType()
    {
        return $this->targetMimeType;
    }

    public function setTargetResourceKey($targetResourceKey)
    {
        $this->targetResourceKey = $targetResourceKey;
    }

    public function getTargetResourceKey()
    {
        return $this->targetResourceKey;
    }
}

class IGDGoogle_Service_Drive_FileList extends IGDGoogle_Collection
{
    public $kind;
    public $nextPageToken;
    protected $collection_key = 'files';
    protected $internal_gapi_mappings = [
    ];
    protected $filesType = 'IGDGoogle_Service_Drive_DriveFile';
    protected $filesDataType = 'array';

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }
}

class IGDGoogle_Service_Drive_GeneratedIds extends IGDGoogle_Collection
{
    public $ids;
    public $kind;
    public $space;
    protected $collection_key = 'ids';
    protected $internal_gapi_mappings = [
    ];

    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setSpace($space)
    {
        $this->space = $space;
    }

    public function getSpace()
    {
        return $this->space;
    }
}

class IGDGoogle_Service_Drive_Permission extends IGDGoogle_Model
{
    public $allowFileDiscovery;
    public $displayName;
    public $domain;
    public $emailAddress;
    public $id;
    public $kind;
    public $photoLink;
    public $role;
    public $type;
    protected $internal_gapi_mappings = [
    ];

    public function setAllowFileDiscovery($allowFileDiscovery)
    {
        $this->allowFileDiscovery = $allowFileDiscovery;
    }

    public function getAllowFileDiscovery()
    {
        return $this->allowFileDiscovery;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setPhotoLink($photoLink)
    {
        $this->photoLink = $photoLink;
    }

    public function getPhotoLink()
    {
        return $this->photoLink;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}

class IGDGoogle_Service_Drive_PermissionList extends IGDGoogle_Collection
{
    public $kind;
    public $nextPageToken;
    protected $collection_key = 'permissions';
    protected $internal_gapi_mappings = [
    ];
    protected $permissionsType = 'IGDGoogle_Service_Drive_Permission';
    protected $permissionsDataType = 'array';

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
}

class IGDGoogle_Service_Drive_Reply extends IGDGoogle_Model
{
    public $action;
    public $content;
    public $createdTime;
    public $deleted;
    public $htmlContent;
    public $id;
    public $kind;
    public $modifiedTime;
    protected $internal_gapi_mappings = [
    ];
    protected $authorType = 'IGDGoogle_Service_Drive_User';
    protected $authorDataType = '';

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAuthor(IGDGoogle_Service_Drive_User $author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;
    }

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }
}

class IGDGoogle_Service_Drive_ReplyList extends IGDGoogle_Collection
{
    public $kind;
    public $nextPageToken;
    protected $collection_key = 'replies';
    protected $internal_gapi_mappings = [
    ];
    protected $repliesType = 'IGDGoogle_Service_Drive_Reply';
    protected $repliesDataType = 'array';

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }

    public function setReplies($replies)
    {
        $this->replies = $replies;
    }

    public function getReplies()
    {
        return $this->replies;
    }
}

class IGDGoogle_Service_Drive_Revision extends IGDGoogle_Model
{
    public $id;
    public $keepForever;
    public $kind;
    public $md5Checksum;
    public $mimeType;
    public $modifiedTime;
    public $originalFilename;
    public $publishAuto;
    public $published;
    public $publishedOutsideDomain;
    public $size;
    protected $internal_gapi_mappings = [
    ];
    protected $lastModifyingUserType = 'IGDGoogle_Service_Drive_User';
    protected $lastModifyingUserDataType = '';

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKeepForever($keepForever)
    {
        $this->keepForever = $keepForever;
    }

    public function getKeepForever()
    {
        return $this->keepForever;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setLastModifyingUser(IGDGoogle_Service_Drive_User $lastModifyingUser)
    {
        $this->lastModifyingUser = $lastModifyingUser;
    }

    public function getLastModifyingUser()
    {
        return $this->lastModifyingUser;
    }

    public function setMd5Checksum($md5Checksum)
    {
        $this->md5Checksum = $md5Checksum;
    }

    public function getMd5Checksum()
    {
        return $this->md5Checksum;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setModifiedTime($modifiedTime)
    {
        $this->modifiedTime = $modifiedTime;
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    public function setOriginalFilename($originalFilename)
    {
        $this->originalFilename = $originalFilename;
    }

    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    public function setPublishAuto($publishAuto)
    {
        $this->publishAuto = $publishAuto;
    }

    public function getPublishAuto()
    {
        return $this->publishAuto;
    }

    public function setPublished($published)
    {
        $this->published = $published;
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setPublishedOutsideDomain($publishedOutsideDomain)
    {
        $this->publishedOutsideDomain = $publishedOutsideDomain;
    }

    public function getPublishedOutsideDomain()
    {
        return $this->publishedOutsideDomain;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }
}

class IGDGoogle_Service_Drive_RevisionList extends IGDGoogle_Collection
{
    public $kind;
    protected $collection_key = 'revisions';
    protected $internal_gapi_mappings = [
    ];
    protected $revisionsType = 'IGDGoogle_Service_Drive_Revision';
    protected $revisionsDataType = 'array';

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setRevisions($revisions)
    {
        $this->revisions = $revisions;
    }

    public function getRevisions()
    {
        return $this->revisions;
    }
}

class IGDGoogle_Service_Drive_StartPageToken extends IGDGoogle_Model
{
    public $kind;
    public $startPageToken;
    protected $internal_gapi_mappings = [
    ];

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setStartPageToken($startPageToken)
    {
        $this->startPageToken = $startPageToken;
    }

    public function getStartPageToken()
    {
        return $this->startPageToken;
    }
}

class IGDGoogle_Service_Drive_User extends IGDGoogle_Model
{
    public $displayName;
    public $emailAddress;
    public $kind;
    public $me;
    public $permissionId;
    public $photoLink;
    protected $internal_gapi_mappings = [
    ];

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setMe($me)
    {
        $this->me = $me;
    }

    public function getMe()
    {
        return $this->me;
    }

    public function setPermissionId($permissionId)
    {
        $this->permissionId = $permissionId;
    }

    public function getPermissionId()
    {
        return $this->permissionId;
    }

    public function setPhotoLink($photoLink)
    {
        $this->photoLink = $photoLink;
    }

    public function getPhotoLink()
    {
        return $this->photoLink;
    }
}

class IGDGoogle_Service_Drive_Drive extends IGDGoogle_Model
{
    public $backgroundImageLink;
    public $colorRgb;
    public $id;
    public $kind;
    public $name;
    public $themeId;
    protected $backgroundImageFileType = 'IGDGoogle_Service_Drive_DriveBackgroundImageFile';
    protected $backgroundImageFileDataType = '';
    protected $capabilitiesType = 'IGDGoogle_Service_Drive_DriveCapabilities';
    protected $capabilitiesDataType = '';

    /**
     * @param IGDGoogle_Service_Drive_DriveBackgroundImageFile
     */
    public function setBackgroundImageFile(IGDGoogle_Service_Drive_DriveBackgroundImageFile $backgroundImageFile)
    {
        $this->backgroundImageFile = $backgroundImageFile;
    }

    /**
     * @return IGDGoogle_Service_Drive_DriveBackgroundImageFile
     */
    public function getBackgroundImageFile()
    {
        return $this->backgroundImageFile;
    }

    public function setBackgroundImageLink($backgroundImageLink)
    {
        $this->backgroundImageLink = $backgroundImageLink;
    }

    public function getBackgroundImageLink()
    {
        return $this->backgroundImageLink;
    }

    /**
     * @param IGDGoogle_Service_Drive_DriveCapabilities
     */
    public function setCapabilities(IGDGoogle_Service_Drive_DriveCapabilities $capabilities)
    {
        $this->capabilities = $capabilities;
    }

    /**
     * @return IGDGoogle_Service_Drive_DriveCapabilities
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function setColorRgb($colorRgb)
    {
        $this->colorRgb = $colorRgb;
    }

    public function getColorRgb()
    {
        return $this->colorRgb;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setThemeId($themeId)
    {
        $this->themeId = $themeId;
    }

    public function getThemeId()
    {
        return $this->themeId;
    }
}

class IGDGoogle_Service_Drive_DriveCapabilities extends IGDGoogle_Model
{
    public $canAddChildren;
    public $canChangeDriveBackground;
    public $canComment;
    public $canCopy;
    public $canDeleteDrive;
    public $canDownload;
    public $canEdit;
    public $canListChildren;
    public $canManageMembers;
    public $canReadRevisions;
    public $canRemoveChildren;
    public $canRename;
    public $canRenameDrive;
    public $canShare;

    public function setCanAddChildren($canAddChildren)
    {
        $this->canAddChildren = $canAddChildren;
    }

    public function getCanAddChildren()
    {
        return $this->canAddChildren;
    }

    public function setCanChangeDriveBackground($canChangeDriveBackground)
    {
        $this->canChangeDriveBackground = $canChangeDriveBackground;
    }

    public function getCanChangeDriveBackground()
    {
        return $this->canChangeDriveBackground;
    }

    public function setCanComment($canComment)
    {
        $this->canComment = $canComment;
    }

    public function getCanComment()
    {
        return $this->canComment;
    }

    public function setCanCopy($canCopy)
    {
        $this->canCopy = $canCopy;
    }

    public function getCanCopy()
    {
        return $this->canCopy;
    }

    public function setCanDeleteDrive($canDeleteDrive)
    {
        $this->canDeleteDrive = $canDeleteDrive;
    }

    public function getCanDeleteDrive()
    {
        return $this->canDeleteDrive;
    }

    public function setCanDownload($canDownload)
    {
        $this->canDownload = $canDownload;
    }

    public function getCanDownload()
    {
        return $this->canDownload;
    }

    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
    }

    public function getCanEdit()
    {
        return $this->canEdit;
    }

    public function setCanListChildren($canListChildren)
    {
        $this->canListChildren = $canListChildren;
    }

    public function getCanListChildren()
    {
        return $this->canListChildren;
    }

    public function setCanManageMembers($canManageMembers)
    {
        $this->canManageMembers = $canManageMembers;
    }

    public function getCanManageMembers()
    {
        return $this->canManageMembers;
    }

    public function setCanReadRevisions($canReadRevisions)
    {
        $this->canReadRevisions = $canReadRevisions;
    }

    public function getCanReadRevisions()
    {
        return $this->canReadRevisions;
    }

    public function setCanRemoveChildren($canRemoveChildren)
    {
        $this->canRemoveChildren = $canRemoveChildren;
    }

    public function getCanRemoveChildren()
    {
        return $this->canRemoveChildren;
    }

    public function setCanRename($canRename)
    {
        $this->canRename = $canRename;
    }

    public function getCanRename()
    {
        return $this->canRename;
    }

    public function setCanRenameDrive($canRenameDrive)
    {
        $this->canRenameDrive = $canRenameDrive;
    }

    public function getCanRenameDrive()
    {
        return $this->canRenameDrive;
    }

    public function setCanShare($canShare)
    {
        $this->canShare = $canShare;
    }

    public function getCanShare()
    {
        return $this->canShare;
    }
}

class IGDGoogle_Service_Drive_DriveBackgroundImageFile extends IGDGoogle_Model
{
    public $id;
    public $width;
    public $xCoordinate;
    public $yCoordinate;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setXCoordinate($xCoordinate)
    {
        $this->xCoordinate = $xCoordinate;
    }

    public function getXCoordinate()
    {
        return $this->xCoordinate;
    }

    public function setYCoordinate($yCoordinate)
    {
        $this->yCoordinate = $yCoordinate;
    }

    public function getYCoordinate()
    {
        return $this->yCoordinate;
    }
}

class IGDGoogle_Service_Drive_DriveList extends IGDGoogle_Collection
{
    public $kind;
    public $nextPageToken;
    protected $collection_key = 'drives';
    protected $drivesType = 'IGDGoogle_Service_Drive_Drive';
    protected $drivesDataType = 'array';

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }

    /**
     * @param IGDGoogle_Service_Drive_Drive[]
     * @param mixed $drives
     */
    public function setDrives($drives)
    {
        $this->drives = $drives;
    }

    /**
     * @return IGDGoogle_Service_Drive_Drive[]
     */
    public function getDrives()
    {
        return $this->drives;
    }
}
