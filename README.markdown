CakePHP Foursquare Datasource
=============================

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
            
            /* And then we call any of the available methods, a simple test method in this case */
            $result = $this->Foursquare->test();

            debug($result); // returns an array with an ok message if Foursquare can be reached

        } 
    } 
    ?>

