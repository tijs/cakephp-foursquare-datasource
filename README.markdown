CakePHP Foursquare Datasource
=============================

**Note: This datasource is not yet compatible with the v2 API, feel free to update it if you need this functionality!**

This datasource acts as a basic wrapper for the [Foursquare API](http://groups.google.com/group/foursquare-api/web/api-documentation?show_cb=1&pli=1) that allows you to interact with the API from Cake as you would with any model.

Installation
------------

1. Place foursquare_source.php in your app/models/datasources folder
2. Add the credentials to database.php
3. Add your datasource and call some methods

### Step 1: Place foursquare_source.php in your app/models/datasources folder

Simply check out or download this repository to your app folder and make sure everything is in the right place.

### Step 2: Add the credentials to database.php

Your database configuration would look something like this:
    
    <?php
    class DATABASE_CONFIG {

        /* these would be your existing DB credentials */
    	var $default = array(
    	    //...
    	);

        /* and here we have your Foursquare credentials */
        var $foursquare = array( 
            'datasource' => 'foursquare', 
            'email' => 'your_foursquare_email_adress', 
            'password' => 'your_foursquare_password', 
        );
        
    }
    ?>

### Step 3: Add your datasource and call some methods

An example where you make your Foursquare data available right into a controller would look something like this:

    <?php 
    class FoursquareController extends AppController {

        /* we don't want annoying warnings about missing tables */
        var $uses = array(); 

        function index(){ 

            /* We initialize the new datasource like so */
            $this->Foursquare = ConnectionManager::getDataSource('foursquare');
            
            /* And then we call any of the available methods, the venues method in this case */
            $result = $this->Foursquare->venues('51.914835', '4.473985', null, 10); // params: lat, long, name, limit

            debug($result); // returns an array with 10 venues near the Boijmans van Beuningen Museum in Rotterdam, the Netherlands

        } 
    } 
    ?>

