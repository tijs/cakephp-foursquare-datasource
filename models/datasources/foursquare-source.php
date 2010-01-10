<?php
/**
 * Foursquare Datasource 0.1
 *
 * Mostly based on the Twitter datasource that hooks up the Twitter API this datasource does the same for Foursquare.
 *
 * For now this is basically a wrapper for the API methods, at some point we might want to get a bit smarter
 * with basic validation and deciding which parameters we choose to send, skip or maybe format first based on
 * the input.
 *
 * This datasource uses Basic Auth since thats just super easy to implement, feel free to
 * rewrite this as an OAUTH authenticated datasource though!
 *
 * Note 1: This datasource implements all but the City methods which, since Foursquare Everywhere, are no
 * longer really relevant.
 *
 * Note 2: A lot of these methods presume some type of multistep user interaction. Please consult the 
 * Foursquare documentation for suggestions on how to handle different use cases:
 *  - http://groups.google.com/group/foursquare-api/web/api-documentation
 *
 * References(Credits):
 * http://bakery.cakephp.org/articles/view/twitter-datasource
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * @author Tijs Teulings <tijs@automatique.nl>
 * @link http://github.com/tijs/cakephp-foursquare-datasource
 * @copyright (c) 2010 Tijs Teulings
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @created January 9, 2010
 * @version 0.1
 */
App::import('Core', array('Xml', 'HttpSocket'));

class FoursquareSource extends DataSource
{
	var $email = "";
	var $password = "";
	var $description = "Foursquare API";
	var $Http = null;

	function __construct($config) {
		parent::__construct($config);
		$this->Http =& new HttpSocket();
		$this->email = $this->config['email'];
		$this->password = $this->config['password'];
	}

	/**
	 * Returns a list of recent checkins from friends.
	 *
	 * When your current latitude and longitude is added distance of each friend (in meters)
	 * is returned as well.
	 *
	 * @param float geolat Optional. Your current latitude.
	 * @param float geolong Optional. Your current longitude.
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function checkins($geolat = null, $geolong = null) {
	    
	    $params = array_filter(compact('geolat', 'geolong')); // they array_filter makes sure we only send parameters with content
	    
		$url = "http://api.foursquare.com/v1/checkins";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Allows you to check-in to a place.
	 *
	 * Please refer to the API documentation for some usage suggestions.
	 *
	 * @param integer vid Optional. ID of the venue where you want to check-in.
	 * @param string venue Optional. if you don't have a venue ID, pass the venue name as a string using this parameter. foursquare will attempt to match it on the server-side
	 * @param string shout Optional. if you don't have a venue ID, pass the venue name as a string using this parameter. foursquare will attempt to match it on the server-side
	 * @param boolean private Optional. "1" means "don't show your friends". "0" means "show everyone"
	 * @param boolean twitter Optional. "1" means "send to Twitter". "0" means "don't send to Twitter"
	 * @param boolean facebook Optional. "1" means "send to Twitter". "0" means "don't send to Twitter"
	 * @param float geolat Optional. Your current latitude. Recommended.
	 * @param float geolong Optional. Your current longitude. Recommended.
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 * @todo Thinking it would be nice to block facebook or twitter posting when private=1, since FS does not do that for you
	 */
	function checkin($vid = null, $venue = null, $shout = null, $private = null, $twitter = null, $facebook = null, $geolat = null, $geolong = null) {
	    
	    $params = array_filter(compact('vid', 'venue', 'shout', 'private', 'twitter', 'facebook', 'geolat', 'geolong'));
	    
		$url = "http://api.foursquare.com/v1/checkin";
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Returns a history of checkins for the authenticated user (across all cities).
	 *
	 * @param integer l Optional. limit of results (default: 20). number of checkins to return
	 * @param integer sinceid Optional. id to start returning results from (if omitted returns most recent results)
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function history($l = null, $sinceid = null) {
	    
	    $params = array_filter(compact('l', 'sinceid'));
	    
		$url = "http://api.foursquare.com/v1/history";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Returns profile information (badges, etc) for a given user. If the user has recent check-in data 
	 * (ie, if the user is self or is a friend of the authenticating user), this data will be returned as well in a <checkin> block.
	 *
	 * @param integer uid Optional  userid for the user whose information you want to retrieve. if you do not specify a 'uid', the authenticated user's profile data will be returned.
	 * @param boolean badges Optional   set to true ("1") to also show badges for this user. by default, this will only show badges from the authenticated user's current city. (default: false)
	 * @param boolean mayor Optional    set to true ("1") to also show venues for which this user is a mayor. by default, this will show mayorships worldwide.
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function user($uid = null, $badges = null, $mayor = null) {
	    
	    $params = array_filter(compact('uid', 'badges', 'mayor'));
	    
		$url = "http://api.foursquare.com/v1/user";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Returns a list of friends. If you do not specify uid, the authenticating user's list of friends will be returned. 
	 * If the friend has allowed it, you'll also see links to their Twitter and Facebook accounts.
	 *
	 * @param integer uid Optional. User id of the person for whom you want to pull a friend graph
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function friends($uid = null) {
	    
	    $params = array_filter(compact('uid'));
	    
		$url = "http://api.foursquare.com/v1/friends";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Get a list of nearby venues based on location
	 *
	 * Be careful using this list as is, sometimes it returns trending venues before nearby ones, so keep an eye on the type!
	 *
	 * @param float geolat Required.  The latitude of your venue.
	 * @param float geolong Required.  The longitude of your venue.
	 * @param string q Optional.  An optional keyword search, which sometimes results in more accurate results.
	 * @param integer l Optional.  Optional result limit (defaults to 10).
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function venues($geolat, $geolong, $q = null, $l = null) {
	    
	    $params = array_filter(compact('geolat', 'geolong', 'q', 'l'));
	    
		$url = "http://api.foursquare.com/v1/venues";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Returns venue data, including mayorship, tips/to-dos and tags.
	 *
	 * @param integer vid Required.  the ID for the venue for which you want information.
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function venue($vid) {
	    
		$url = "http://api.foursquare.com/v1/venue";
		return $this->__process($this->Http->get($url, array('vid'=>$vid), $this->__getAuthHeader()));
		
	}

	/**
	 * Allows you to add a new venue.
	 *
	 * @param string name Required.  the name of the venue
	 * @param string address Required.  the address of the venue (e.g., "202 1st Avenue")
	 * @param string crossstreet Required.  the cross streets (e.g., "btw Grand & Broome")
	 * @param string city Required.  the city name where this venue is
	 * @param string state Required.   the state where the city is (for international venues use country name here)
	 * @param string zip Optional.  the ZIP code for the venue
	 * @param string phone Optional.   the phone number for the venue
	 * @param float geolat Optional.  the latitude for the venue (recommended).
	 * @param float geolong Optional.  the longitiude for the venue (recommended).
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 * @todo In Foursquare everywhere there are less required fields, for now i'll just leave them here 
	 * for the sake of clean data. Feel free to change that in your version
	 */
	function addvenue($name, $address, $crossstreet, $city, $state, $zip = null, $phone = null, $geolat = null, $geolong = null) {

	    $params = array_filter(compact('name', 'address', 'crossstreet', 'city', 'state', 'zip', 'phone', 'geolat', 'geolong'));
	    
		$url = "http://api.foursquare.com/v1/addvenue";
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Allows you to flag/propose a change to a venue.
	 *
	 * @param integer vid Required.  the ID of the venue
	 * @param string name Required.  the name of the venue
	 * @param string address Required.  the address of the venue (e.g., "202 1st Avenue")
	 * @param string crossstreet Required.  the cross streets (e.g., "btw Grand & Broome")
	 * @param string city Required.  the city name where this venue is
	 * @param string state Required.   the state where the city is (for international venues use country name here)
	 * @param string zip Optional.  the ZIP code for the venue
	 * @param string phone Optional.   the phone number for the venue
	 * @param float geolat Optional.  the latitude for the venue (recommended).
	 * @param float geolong Optional.  the longitiude for the venue (recommended).
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function venue_proposeedit($vid, $name, $address, $crossstreet, $city, $state, $zip = null, $phone = null, $geolat = null, $geolong = null) {

	    $params = array_filter(compact('vid', 'name', 'address', 'crossstreet', 'city', 'state', 'zip', 'phone', 'geolat', 'geolong'));
	    
		$url = "http://api.foursquare.com/v1/venue/proposeedit";
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
		
	}

 	/**
	 * Allows you to flag/propose a change to a venue.
	 *
	 * @param integer vid Required.  the ID of the venue
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function venue_flagclosed($vid) {

		$url = "http://api.foursquare.com/v1/venue/flagclosed";
		return $this->__process($this->Http->post($url, array('vid'=>$vid), $this->__getAuthHeader()));
		
	}

	/**
	 * Returns a list of tips near the area specified. (The distance returned is in meters).
	 *
	 * @param float geolat Required.  The latitude of your venue.
	 * @param float geolong Required.  The longitude of your venue.
	 * @param integer l Optional.  Optional  limit of results (defaults to 30).
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function tips($geolat, $geolong, $l = null) {
	    
	    $params = array_filter(compact('geolat', 'geolong', 'l'));
	    
		$url = "http://api.foursquare.com/v1/tips";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}


	/**
	 * Allows you to add a new tip or to-do at a venue.
	 *
	 * @param integer vid Required.  the venue where you want to add this tip
	 * @param string text Required.  the text of the tip or to-do item
	 * @param string type Required.  specify one of 'tip' or 'todo' (default: tip)
	 * @param float geolat Optional.  the latitude for the venue (recommended).
	 * @param float geolong Optional.  the longitiude for the venue (recommended).
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function addtip($vid, $text, $type, $geolat = null, $geolong = null) {

	    $params = array_filter(compact('vid', 'text', 'type', 'geolat', 'geolong'));
	    
		$url = "http://api.foursquare.com/v1/addtip";
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Allows you to mark a tip as a to-do item.
	 *
	 * @param integer tid Required.  the tip that you want to mark to-do
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function tip_marktodo($tid) {
	    
		$url = "http://api.foursquare.com/v1/tip/marktodo";
		return $this->__process($this->Http->post($url, array('tid'=>$tid), $this->__getAuthHeader()));
		
	}
	
	/**
	 * Allows you to mark a tip as done.
	 *
	 * @param integer tid Required.  the tip that you want to mark done
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function tip_markdone($tid) {
	    
		$url = "http://api.foursquare.com/v1/tip/markdone";
		return $this->__process($this->Http->post($url, array('tid'=>$tid), $this->__getAuthHeader()));
		
	}
	
	/**
	 * Shows you a list of users with whom you have a pending friend request 
	 * (ie, they've requested to add you as a friend, but you have not approved).
	 *
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function friend_requests() {
	    
		$url = "http://api.foursquare.com/v1/friend/requests";
		return $this->__process($this->Http->get($url, array(), $this->__getAuthHeader()));
		
	}

	/**
	 * Approves a pending friend request from another user. On success, returns the <user> object.
	 *
	 * @param integer uid Required.   the user ID of the user who you want to approve
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function friend_approve($uid) {
	    
		$url = "http://api.foursquare.com/v1/friend/approve";
		return $this->__process($this->Http->get($url, array('uid'=>$uid), $this->__getAuthHeader()));
		
	}

	/**
	 * Denies a pending friend request from another user. On success, returns the <user> object.
	 *
	 * @param integer uid Required.   the user ID of the user who you want to deny
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function friend_deny($uid) {
	    
		$url = "http://api.foursquare.com/v1/friend/deny";
		return $this->__process($this->Http->get($url, array('uid'=>$uid), $this->__getAuthHeader()));
		
	}

	/**
	 * Sends a friend request to another user. On success, returns the <user> object.
	 *
	 * @param integer uid Required.   the user ID of the user to whom you want to send a friend request
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function friend_sendrequest($uid) {
	    
		$url = "http://api.foursquare.com/v1/friend/sendrequest";
		return $this->__process($this->Http->get($url, array('uid'=>$uid), $this->__getAuthHeader()));
		
	}

	/**
	 * When passed a free-form text string, returns a list of matching <user> objects. 
	 * The method only returns matches of people with whom you are not already friends.
	 *
	 * @param string q Required.   the string you want to use to search firstnames and lastnames
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function findfriends_byname($q) {
	    
		$url = "http://api.foursquare.com/v1/findfriends/byname";
		return $this->__process($this->Http->get($url, array('q'=>$q), $this->__getAuthHeader()));
		
	}

	/**
	 * When passed phone number(s), returns a list of matching <user> objects. 
	 * The method only returns matches of people with whom you are not already friends. 
	 * You can pass a single number as a parameter, or you can pass multiple numbers separated by commas.
	 *
	 * @param string q Required.   the string you want to use to search for phone numbers
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function findfriends_byphone($q) {
	    
		$url = "http://api.foursquare.com/v1/findfriends/byphone";
		return $this->__process($this->Http->get($url, array('q'=>$q), $this->__getAuthHeader()));
		
	}

	/**
	 * When passed a Twitter name (user A), returns a list of matching <user> objects that correspond to user A's friends on Twitter. 
	 * The method only returns matches of people with whom you are not already friends.
     * If you don't pass in a Twitter name, it will attempt to use the Twitter name associated with the authenticating user.
	 *
	 * @param string q Optional.   the Twitter name you want to use to search
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function findfriends_bytwitter($q = null) {

	    $params = array_filter(compact('q'));
	    
		$url = "http://api.foursquare.com/v1/findfriends/bytwitter";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Allows you to change notification options for yourself (self) globally as well as 
	 * for each individual friend (identified by their uid).
     *
     * For example: To set pings on for a user identified by UID 33: "33=on". 
     * To set pings to 'goodnight' for yourself: "self=goodnight".
	 *
	 * @param string self Optional.   the ping status for yourself (globally). possible values are on, off and goodnight.
	 * @param array uid Optional.   set the ping status for a friend. possible values are on and off.
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function settings_setpings($self = null, $uid = null) {

	    $params = array_filter(compact('self', $uid));

		$url = "http://api.foursquare.com/v1/settings/setpings";
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
		
	}

	/**
	 * Returns the string "ok" (if we can reach the Foursquare servers that is)
     *
	 * @see http://groups.google.com/group/foursquare-api/web/api-documentation
	 */
	function test() {

		$url = "http://api.foursquare.com/v1/test";
		return $this->__process($this->Http->get($url));
		
	}	

	/**
	 * Credentials array for method with mandatory auth
	 * @return array credentials
	 */
	function __getAuthHeader() {
		return array('auth' => array('method' => 'Basic',
									 'user' => $this->email,
									 'pass' => $this->password
		)
		);
	}

	/**
	 *
	 * @param string data to process
	 * @return array Twitter API response
	 */
	function __process($response) {
		$xml = new XML($response);
		$array = $xml->toArray();

		$xml->__killParent();
		$xml->__destruct();
		$xml = null;
		unset($xml);

		return $array;
	}
}
