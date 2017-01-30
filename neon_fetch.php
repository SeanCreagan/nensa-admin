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
    $search = array( 
        'method' => 'account/listAccounts', 
        'columns' => array(
            'standardFields' => array('Account ID', 'First Name', 'Last Name', 'Email 1', 'City', 'State' ),
        ),
        'page' => array(
            'currentPage' => 1,
            'pageSize' => 200,
            'sortColumn' => 'Last Name',
            'sortDirection' => 'ASC',
        ),
    );

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
    if ( !empty( $search['criteria'] ) ) {
        $result = $neon->search($search);
        //$result_1 = $neon->go( array( 'method' => 'account/retrieveIndividualAccount', 'parameters' => array('accountId'=>$searchCriteria['accountID'])));
        $message = 'No results match your search.';
    } else {
        $result = null;
        $message = 'You must specify search criteria.';
    }

    /* Logout - terminate API session with the server */
    $neon->go( array( 'method' => 'common/logout' ) );

  } else {
      $result = null;
      $message = 'There was a problem connecting to NeonCRM.';
  }

  ?>



  <h1>NENSA Member Lookup</h1>
  </br>
  <form action=# method="POST" style="background-color: GAINSBORO;">
    <table class="form-table"> 
      <tr >
        <th style="padding-left: 12px;">NENSA ID</th>
        <th></th>
        <th style="padding-left: 12px;">Last Name</th>
        <th style="padding-left: 12px;">First Name</th>
      </tr>
      <tr valign="top">
        <td width="5%">
          <input type="text" name="accountID" value="<?php echo htmlentities( $searchCriteria['accountID'] ); ?>"/>
        </td>
        <td width="5%">OR</td>
        <td width="5%">
          <input type="text" name="lastName" value="<?php echo htmlentities( $searchCriteria['lastName'] ); ?>" />
        <td width="5%">
          <input type="text" name="firstName" value="<?php echo htmlentities( $searchCriteria['firstName'] ); ?>" />
        </td><td></td>
      </tr>
    </table>
      <p class="submit" style="padding-left: 12px;">
        <input type="submit" class="button-primary" value="<?php _e('Submit', 'nensa_admin') ?>" /></br>
      </p>
  </form>
  </br>
  <hr>

  <?php
  /**
   * Iterate through API results
   *******************************************/
  ?>
  <?php if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ): ?>
  <table class="table table-striped" style="border-style: solid; border-width: 1px; background-color: white;">
      <thead>
          <tr>
              <th align="left">NENSA #</th>
              <th align="left">Name</th>
              <th align="left">Email</th>
              <th align="left">Location</th>
          </tr>
      </thead>
      <tbody style="background-color: GAINSBORO;">
          <?php foreach($result['searchResults'] as $r): ?>
          <tr >
              <td width="15%"><?php echo $r['Account ID']; ?> 
              <td width="15%"><?php echo $r['First Name']; ?> <?php echo $r['Last Name']; ?></td>
              <td width="15%"><?php echo $r['Email 1']; ?></td>
              <td width="15%"><?php echo $r['City']; ?> <?php echo $r['State']; ?></td>
          </tr>
          <?php endforeach; ?>
      </tbody>
  </table>
  </br><?php //print_r($result_1); ?></br>
  <?php else: ?>
      <p><?php echo $message; ?></p>
  <?php endif; ?>

<?php
}
?>