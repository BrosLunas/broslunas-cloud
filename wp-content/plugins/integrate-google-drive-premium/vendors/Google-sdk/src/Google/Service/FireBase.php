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
 * Service definition for Firebase (v1).
 *
 * <p>
 * Lets you create, inspect, and manage goo.gl short URLs</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/url-shortener/v1/getting_started" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class IGDGoogle_Service_Firebase extends IGDGoogle_Service
{
  /** Manage your goo.gl short URLs. */
  const FIREBASE =
      "https://firebasedynamiclinks.googleapis.com/v1/shortLinks";

  public $url;
  

  /**
   * Constructs the internal representation of the Firebase service.
   *
   * @param IGDGoogle_Client $client
   */
  public function __construct(IGDGoogle_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://firebasedynamiclinks.googleapis.com';
    $this->servicePath = 'v1/shortLinks';
    $this->version = 'v1';
    $this->serviceName = 'firebase';

    $this->url = new IGDGoogle_Service_Firebase_Url_Resource(
        $this,
        $this->serviceName,
        'url',
        array(
          'methods' => array(
            'insert' => array(
              'path' => 'url',
              'httpMethod' => 'POST',
              'parameters' => array(
                'longDynamicLink' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'suffix' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
}


/**
 * The "url" collection of methods.
 * Typical usage is:
 *  <code>
 *   $firebaseService = new IGDGoogle_Service_Firebase(...);
 *   $url = $firebaseService->url;
 *  </code>
 */
class IGDGoogle_Service_Firebase_Url_Resource extends IGDGoogle_Service_Resource
{


  /**
   * Creates a new short URL. (url.insert)
   *
   * @param IGDGoogle_Url $postBody
   * @param array $optParams Optional parameters.
   * @return IGDGoogle_Service_Firebase_Url
   */
  public function insert($longDynamicLink, $params = array())
  {    
    $params = array('longDynamicLink' => $longDynamicLink);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "IGDGoogle_Service_Firebase_Url");
  }

}



class IGDGoogle_Service_Firebase_Url extends IGDGoogle_Model
{
  protected $internal_gapi_mappings = array(
  );

  public $shortLink;
  public $previewLink;

  public function setShortLink($shortLink)
  {
    $this->shortLink = $shortLink;
  }
  public function getShortLink()
  {
    return $this->shortLink;
  }
  public function setPreviewLink($previewLink)
  {
    $this->previewLink = $previewLink;
  }
  public function getPreviewLink()
  {
    return $this->previewLink;
  }
}