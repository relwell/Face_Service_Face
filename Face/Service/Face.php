<?php

require_once 'Zend/Rest/Client.php';
require_once 'Zend/Registry.php';
require_once '../PhotoModel.php';
require_once '../UserModel.php';

class Face_Service_Face extends Zend_Service_Abstract
{

    public $apiKey;
    public $apiSecret;
    protected $_rest;
    
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->_rest = new Zend_Rest_Client('http://api.face.com');
    }
    
    protected function _handleResponse($apiUrl, $options)
    {
        $options['api_key'] = $this->apiKey;
        $options['api_secret'] = $this->apiSecret;
        
        $response = $this->_rest->restGet($apiUrl, $options);
        
        $jsonObject = Zend_Json::decode($response, Zend_Json::TYPE_OBJECT);
        
        $this->_recordUsage($jsonObject);
        
        if (property_exists($jsonObject, 'status') && $jsonObject->status != 'success') {
            $this->_restError($jsonObject, __METHOD__);
        }
    }
    
    // @todo: support raw image data
    public function facesDetect(array $imageUrls, $format='json', $callback = null)
    {
        $apiUrl = '/faces/detect.'.$format;
        
        $this->checkLimit($imageUrls);
        
        $options = array('urls'=>implode(',',$imageUrls));
        
        $this->_addIfExists('callback', $callback, $options);
        $this->_addIfExists('callback_url', $callbackUrl, $options);

        $jsonObject  = $this->_handleResponse($apiUrl, $options);
        
        return Face_PhotoModel::getPhotosFromResponse($jsonObject);
    }

    public function facesRecognize(array $userIds, array $imageUrls, $train=false, $defaultNamespace=null,
                                   $format='json', $callback=null, $callbackUrl=null, $userAuth=null)
    {
        $apiUrl = '/faces/recognize.'.$format;

        $this->checkLimit($imageUrls);
        $options = array('uids'=>implode(',', $userIds),
                         'urls'=>implode(',', $imageUrls));
        
        $this->_addIfExists('train', $train, $options);
        $this->_addIfExists('namespace', $defaultNamespace, $options);
        $this->_addIfExists('callback', $callBack, $options);
        $this->_addIfExists('callback_url', $callBackUrl, $options);        
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        $jsonObject  = $this->_handleResponse($apiUrl, $options);
        
        // here is where we might want to handle warnings if !empty($jsonObject->no_training_set)...
        
        return Face_PhotoModel::getPhotosFromResponse($jsonObject);
    }
    
    public function facesTrain($userIds, $defaultNamespace=null, $format='json', $callback = null, $callbackUrl=null, $userAuth=null)
    {
        $apiUrl = '/faces/train.'.$format;
        
        if (count($userIds) > 1 && $callbackUrl === null) {
            throw new Exception ('Exception in '.__METHOD__.' -- faces.train requires a callback URL for calls with multiple UIDs');
        }
        
        $this->checkLimit($imageUrls);
        $options = array('uids'=>implode(',', $userIds));
        
        $this->_addIfExists('namespace', $defaultNamespace, $options);
        $this->_addIfExists('callback', $callBack, $options);
        $this->_addIfExists('callback_url', $callBackUrl, $options);        
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        $jsonObject  = $this->_handleResponse($apiUrl, $options);
        
        $retval = array();
        
        foreach (get_class_vars($jsonObject) as $group)
        {
            if (in_array($group, array('no_training_set', 'created', 'updated', 'unchanged', 'in_progress')) {
                $retval[$group] = Face_Model_UserModel::getUsersFromResponse($jsonObject->{$group});    
            }
        }
        
        return $retval;
    }
    
    public function facesStatus($userIds, $defaultNamespace=null, $format='json', $callback=null, $callbackUrl=null, $userAuth=null)
    {
        $apiUrl = '/faces/status.'.$format;
        
        $this->checkLimit($imageUrls);
        
        $options = array('uids'=>implode(',', $userIds));
        
        $this->_addIfExists('namespace', $defaultNamespace, $options);
        $this->_addIfExists('callback', $callBack, $options);
        $this->_addIfExists('callback_url', $callBackUrl, $options);        
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        $jsonObject  = $this->_handleResponse($apiUrl, $options);
        
        return Face_Model_UserModel::getUsersFromResponse($jsonObject->user_statuses);    
    }
    
    
    // @todo it's really get.json, get.xml, etc. crap...
    public function tagsGet($userIds=null, $urls=null, $photoIds=null, $order=null, $limit=5,
                            $together=false, $filter=false, $format='json', $callback=null, $userAuth=null)
    {
        $apiUrl = '/tags/get.'.$format;
        
        $this->_addIfExists('uids', $userIds, $options);
        $this->_addIfExists('urls', $urls, $options);
        $this->_addIfExists('pids', $photoIds, $options);
        $this->_addIfExists('order', $order, $options);
        $this->_addIfExists('limit', $limit, $options);
        $this->_addIfExists('together', $together, $options);
        $this->_addIfExists('filter', $filter, $options);
        $this->_addIfExists('callback', $callBack, $options);
        $this->_addIfExists('callback_url', $callBackUrl, $options);        
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        $jsonObject = $this->_handleResponse($apiUrl, $options);
        
        $photos = Face_Model_PhotoModel::getPhotosFromResponse($jsonObject);
    }
    
    public function tagsAdd($photoUrl, $x, $y, $width, $taggedUserId, $taggingUserId,
                            $label=null, $format='json', $callback=null, $callbackUrl=null
                            $password=null, $userAuth=null)
    {
        $apiUrl = '/tags/add/';
        
        $this->_addIfExists('url', $photoUrl, $options);
        $this->_addIfExists('x', $x, $options);
        $this->_addIfExists('y', $y, $options);
        $this->_addIfExists('width', $width, $options);
        $this->_addIfExists('uid', taggedUserId, $options);
        $this->_addIfExists('tagger_id', $taggingUserId, $options);
        $this->_addIfExists('label', $label, $options);
        $this->_addIfExists('format', $format, $options);
        $this->_addIfExists('callback', $callBack, $options);
        $this->_addIfExists('callback_url', $callBackUrl, $options);
        $this->_addIfExists('password', $password, $options);
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        $jsonObject = $this->_handleResponse($apiUrl, $options);
    }
    
    public function tagsRemove(array $tagIds, $format='json', $callback=null, $password=null, $userAuth=null)
    {
        $this->_addIfExists('tids', $tagIds, $options);
        $this->_addIfExists('format', $format, $options);
        $this->_addIfExists('callback', $callBack, $options);
        
        if ($userAuth) {
            $options['user_auth'] = $this->_prepareAuth($userAuth);
        }
        
        return $this->_handleResponse($apiUrl, $options);
        
        // could do something with removed tags here instead of just returning
    }
    
    public function accountLimits($format='json', $callback=null)
    {
        $apiUrl = '/account/limits.'.$format;
        $this->_addIfExists('callback', $callBack, $options);
        
        return $this->_handleResponse($apiUrl, $options;)
    }
    
    public function accountUsers(array $namespaces)
    {
        $apiUrl = '/account/users.'.$format;
        $options = array('namespaces'=>implode(',', $namespaces));
        
        return $this->_handleResponse($apiUrl, $options;)
    }
    
    private function _recordUsage(stdClass $jsonObject)
    {
        Zend_Registry::set('usage', $jsonObject->usage);
    }
    
    /**
     *  return a comma-separated set of key-value pairs with the key separated by the value with a colon
     */
    private function _prepareAuth(array $authArray)
    {
        // fb_user : userid & fb_session:fb_sessionid
        // and/or twitter_user:twitter_user_id, twitter_password:[twitter_password]
        // or twitter_oauth_user:[oauthuser],twitter_oauth_secret:[oauthsecret],twitter_oauth_token:[twitteroauthtoken]
        
        $fields = array();
        foreach ($authArray as $key=>$value)
        {
            $fields[] = "{$key}:{$value}";
        }
        return implode(',', $fields);
        
        // @TODO: validate data
    }
    
    public function checkLimit($imageUrls)
    {
        $count = is_array($images) ? count($images) : count($imageUrls);
        
        if (Zend_Registry::isRegistered('usage') && $count > Zend_Registry::get('usage')->remaining ) {
            throw new Exception("Number of photos requested is greater than API limit. API resets at ".Zend_Registry::get('usage')->reset_time_text);
        }
    }
    
    protected function _restError(stdClass $jsonObject, $funcName)
    {
        throw new Exception("API call for {$funcName} was unsuccessful: {$jsonObject->error_message} (Error Code {$jsonObject->error_code})");
    }
    
    protected function _addIfExists($fieldname, $field, array $arr)
    {
        if ($field !== null) {
            $arr[$fieldname] = is_array($field) ? implode(',', $field) : $field;
        }
    }
}
