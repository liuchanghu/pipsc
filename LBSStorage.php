<?php

/**
 * @class         Class LBSStorage
 * @brief         cloud search and save
 *
 * @author        kevin.liu@century21cn.com
 * @copyright (C) 2017 Century21cn All Rights Reserved.
 * @version       1.0
 */
class LBSStorage
{
    const DATA_URL = '/datamanage/data/';
    const DATA_CREATE = 'create';
    const DATA_UPDATA = 'update';
    const DATA_DELETE = 'delete';

    const DATA_SEARCH_URL = '/datasearch/';
    const DATA_SEARCH_AROUND = 'around?'; 
    const DATA_SEARCH_ID = 'id?';
    const DATA_SEARCH_LIST = 'list?';

    /**
     * @name httpGet
     * @brief   Get
     *
     * @author   kevin.liu@century21cn.com
     * @param $url
     * @retval
     * @version  1.0
     * @date     2017-11-02
     */
    private function httpGet($url, $param)
    {
        if (is_array($param)) {
            $url .= http_build_query($param);
        } else if (is_string($param)) {
            $url .= $param;
        }
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return json_decode($sContent, true);
        } else {
            return false;
        }
    }

    /**
     * @name httpPost
     * @brief   Post
     *
     * @author   kevin.liu@century21cn.com
     * @param $url
     * @retval
	 * @return string content
     * @version  1.0
     * @date     2017-11-02
     */	 
    private function httpPost($url, $param)
    {
        $strPOST = '';
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return json_decode($sContent, true);
        } else {
            return false;
        }
    }

    /**
     * @name setDateParam
     * @brief   setDateParam
     *
     * @author   kevin.liu@century21cn.com
     * @param $param
     * @retval
	 * @return array
     * @version  1.0
     * @date     2017-11-02
     */	 
	protected static function setDateParam($param, $isReturn = false)
    {
        $data = array();
        $data['key'] = $GLOBALS['config']['platform']['amap']['rest_key'];
        $data['tableid'] = $GLOBALS['config']['platform']['amap']['tableid'];
        if (empty($isReturn)) {
            $data['data'] = json_encode($param);
            return $data;
        } else {
            return $data;
        }
    }

	/**
     * @name dataCreate
     * @brief   dataCreate
     *
     * @author   kevin.liu@century21cn.com
     * @param $param
     * @retval
	 * @return _id
     * /cloudstorage/#yuntureference_creatdata
     * @version  1.0
     * @date     2017-11-02
     */	 
    public static function dataCreate($param)
    {
        $rs = self::httpPost(self::DATA_URL . self::DATA_CREATE, self::setDateParam($param));
        if ($rs['status'] == 1) {
            return $rs['_id'];
        } else {
            throw new Exception($rs['info'], 1);
        }
    }

    /**
     * @name dataUpdate
     * @brief   dataUpdate
     *
     * @author   kevin.liu@century21cn.com
     * @param $param
     * @retval
	 * @return string
	 * /cloudstorage/#yuntureference_updatedata
	 * @version  1.0
     * @date     2017-11-02
     */	 
    public static function dataUpdate($param)
    {
        $rs = self::httpPost(self::DATA_URL . self::DATA_UPDATA, self::setDateParam($param));
        if ($rs['status'] == 1) {
            return true;
        } else {
            throw new Exception($rs['info'], 1);
        }
    }


	/**
     * @name dataDelete
     * @brief   dataDelete
     *
     * @author   kevin.liu@century21cn.com
     * @param $ids
     * @retval
	 * @return mixed
     * /cloudstorage/#yuntureference_deletedata
     * @version  1.0
     * @date     2017-11-02
     */	 
    public static function dataDelete($ids)
    {
        $data = self::setDateParam(null, true);
        $data['ids'] = $ids;
        $rs = self::httpPost(self::DATA_URL . self::DATA_DELETE, $data);
        if ($rs['status'] == 1) {
            return $rs;
        } else {
            throw new Exception($rs['info'], 1);
        }
    }

 	/**
     * @name dataSearchAround
     * @brief   search data around
     *
     * @author   kevin.liu@century21cn.com
     * @param null $keywords
     * @param null $center
     * @param int $radius
     * @param null $filter
     * @param null $sortrule
     * @param int $page
     * @param int $limit
     * /cloudsearch/#yuntureference_roundsearch
	 * @version  1.0
     * @date     2017-11-02
     */
    public static function dataSearchAround($center, $keywords = null, $radius = 3000, $filter = null, $sortrule = null, $page = 1, $limit = 100)
    {
        $data = self::setDateParam(null, true);
        if (!empty($keywords)) {
            $data['keywords'] = $keywords;
        }
        if (!empty($filter)) {
            $data['filter'] = $filter;
        }
        if (!empty($sortrule)) {
            $data['sortrule'] = $sortrule;
        }
        $data['center'] = $center;
        $data['radius'] = $radius;
        $data['limit'] = $limit;
        $data['page'] = $page;
        $rs = self::httpGet(self::DATA_SEARCH_URL . self::DATA_SEARCH_AROUND, $data);
        if ($rs['status'] == 1) {
            return $rs;
        } else {
            throw new Exception($rs['info'], 1);
        }
    }

   /**
     * @name dataSearchId
     * @brief   search data id
     *
     * @author   kevin.liu@century21cn.com
     * @param $id
     * @return bool
     * /cloudsearch/#yuntureference_idsearch
     * @version  1.0
     * @date     2017-11-02
     */
    public static function dataSearchId($id)
    {
        $data = self::setDateParam(null, true);
        $data['_id'] = $id;
        $rs = self::httpGet(self::DATA_SEARCH_URL . self::DATA_SEARCH_ID, $data);
        if ($rs['status'] == 1) {
            return $rs;
        } else {
            throw new Exception($rs['info'], 1);
        }
    }

    /**
     * @name dataSearchList
     * @brief   data search result, by HTTP/GET
     *
     * @author   kevin.liu@century21cn.com
     * @param null $filter
     * @param null $sortrule
     * @param int $page
     * @param int $limit
     * @return bool
     * /cloudsearch/#yuntureference_datalist
     * @version  1.0
     * @date     2017-11-02
     */
    public static function dataSearchList($filter = null, $sortrule = null, $page = 1, $limit = 100)
    {
        $data = self::setDateParam(null, true);
        if (!empty($filter)) {
            $data['filter'] = $filter;
        }
        if (!empty($sortrule)) {
            $data['sortrule'] = $sortrule;
        }
        $data['limit'] = $limit;
        $data['page'] = $page;
        $rs = self::httpGet(self::DATA_SEARCH_URL . self::DATA_SEARCH_LIST, $data);
        if ($rs['status'] == 1) {
            return $rs;
        } else {
            throw new Exception($rs['info'], 1);
        }
    }
}