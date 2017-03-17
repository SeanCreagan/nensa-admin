<?php 

function search_neon_for_racer() {

  /* Include the NeonCRM PHP Library */
  require_once('neon.php');

  /**
   * POST Data
   **********************************************/

  /* Retrieve and sanitize POST data */
  $arguments = array(
      'accountID' => FILTER_SANITIZE_SPECIAL_CHARS,
      'firstName' => FILTER_SANITIZE_SPECIAL_CHARS,
      'lastName'  => FILTER_SANITIZE_SPECIAL_CHARS,
      'email'     => FILTER_SANITIZE_EMAIL,
  );
  $searchCriteria = filter_input_array( INPUT_POST, $arguments );

  /**
   * API Authentication
   *******************************************/

  /* Instantiate the Neon class */
  $neon = new Neon();

  /* Set your API credentials */
  $credentials = array(
      'orgId' => NEON_USER,
      'apiKey' => NEON_APIKEY
  );

  /* Authenticate with the API */
  $loginResult = $neon->login($credentials);

  /* Upon successful authentication, proceed with building the search query */
  if ( isset( $loginResult['operationResult'] ) && $loginResult['operationResult'] == 'SUCCESS' ) {

    /**
     * Search Query
     *************************************************/

    /* Formulate the search query */
    //membership/listMemberships   User Full Name (F)
    // 'method' => 'account/listAccounts',
    //             'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Gender', 'Email 1', 'City', 'State' ),

    $search = array( 
        'method' => 'account/listAccounts', 
        'columns' => array(
            'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Gender', 'Email 1', 'City', 'State' ),
            'customFields' => array(101,170,108),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'DESC',
        ),
    );

    $search1 = array( 
        'method' => 'membership/listMemberships', 
        'columns' => array(
            'standardFields' => array('Account ID', 'Full Name (F)', 'Company Name', 'DOB Month', 'DOB Year', 'State', 'Membership Name', 'Membership Cost','Membership Expiration Date', 'Membership Start Date', 'Membership Enrollment Date' ),
            'customFields' => array(136,171),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Account ID',
            'sortDirection' => 'DESC',
        ),
    );

    // Stanard API call "go" with example on how to fetch numbers for customer
    // field mapping.  Swap "Membership"  with "Account"
    $go2 = array( 
      'method' => 'common/listCustomFields', 
      'parameters' => array(
        'searchCriteria.component' => 'Account',
        ),
      );

    // Use the following single line for complete list of accounts
    // Be sure to comment out search array setup 3 lines down
    //$search['criteria'][] = array( 'Account ID', 'NOT_BLANK', '');
    $search1['criteria'][] = array( 'Last Name', 'NOT_BLANK', '');
    
    /* Some search criteria are variable based on our POST data. Add them to the query if applicable */
    
    if ( isset( $searchCriteria['accountID'] ) && !empty( $searchCriteria['accountID'] ) ) {
        $search['criteria'][] = array( 'Account ID', 'EQUAL', $searchCriteria['accountID'] );
    }
    if ( isset( $searchCriteria['firstName'] ) && !empty( $searchCriteria['firstName'] ) && isset( $searchCriteria['lastName'] ) && !empty( $searchCriteria['lastName'] )){
        $search['criteria'][] = array( 'First Name', 'EQUAL', $searchCriteria['firstName'] );
        $search['criteria'][] = array( 'Last Name', 'EQUAL', $searchCriteria['lastName'] );
    }
    

    /**
     * Execute search
     **************************************************/ 

    /* If there are search criteria present, execute the search query */

    $result = $neon->search($search1);
    $result_1 = $neon->go($go2);

    $message = 'Last Date Processed:' . date(DATE_RFC2822);
    

    /* Logout - terminate API session with the server */
    $neon->go( array( 'method' => 'common/logout' ) );

  } else {
      $result = null;
      $message = 'There was a problem connecting to NeonCRM.';
  }

  ?>



  <h1>NENSA Member Season Update From NEON</h1>
  </br>
  <form action=# method="POST" >
    <input type="submit" class="button-primary" value="<?php _e('Load Member Season Table', 'nensa_admin') ?>" /></br>
  </form>
  </br>
  <p><?php echo 'Date Last Loaded: ' . date(DATE_RFC2822); ?></p>
  <hr>

  <?php
  /**
   * Iterate through API results
   *******************************************/
  ?>
  <?php if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ): ?>
    </br><?php print ($result['page']['totalResults']); ?></br>
    </br><?php print_r(array_keys($result['searchResults'][0])); ?></br>
  </br><?php print_r($result['searchResults'][0]['Membership Expiration Date']); ?></br>
    </br><?php print(sizeof($result['searchResults'])); ?></br>
    </br><?php print_r($result_1); ?></br>
    </br><?php print_r(array_keys($result_1)); ?></br>
    </br><?php //print_r(array_keys($result_1['individualAccount'])); ?></br>
    </br><?php //print_r($result_1['individualAccount']['lastModifiedDateTime']); ?></br>
    </br><?php //print_r($result_1['individualAccount']['accountId']); ?></br>
    </br><?php //print_r(array_keys($result_1['individualAccount']['primaryContact'])); ?></br>
  <?php else: ?>
      <p><?php echo $message; ?></p>
  <?php endif; ?>

<?php
}
?>