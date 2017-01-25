<?php 

function fetch_racer_from_neon() {


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
    if ( isset( $searchCriteria['firstName'] ) && !empty( $searchCriteria['firstName'] ) ) {
        $search['criteria'][] = array( 'First Name', 'EQUAL', $searchCriteria['firstName'] );
    }
    if ( isset( $searchCriteria['lastName'] ) && !empty( $searchCriteria['lastName'] ) ) {
        $search['criteria'][] = array( 'Last Name', 'EQUAL', $searchCriteria['lastName'] );
    }
    if ( isset( $searchCriteria['email'] ) && !empty( $searchCriteria['email'] ) ) {
        $search['criteria'][] = array( 'Email', 'EQUAL', $searchCriteria['email'] );
    }

    /**
     * Execute search
     **************************************************/ 

    /* If there are search criteria present, execute the search query */
    if ( !empty( $search['criteria'] ) ) {
        $result = $neon->go( array( 'method' => 'account/retrieveIndividualAccount', 'parameters' => array('accountId'=>$searchCriteria['accountID'])));
        // print_r($result);
        //$result = $neon->search($search);
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



                <h1>Account Search</h1>
                </br>
                <form action=# method="POST" class="form-inline">
                    <fieldset>
                        <legend>Search Criteria</legend>
                            <div class="form-group">
                                <label>NENSA #</label>
                                <input type="text" class="form-control" name="accountID" value="<?php echo htmlentities( $searchCriteria['accountID'] ); ?>"/>
                            </div></br>
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="firstName" value="<?php echo htmlentities( $searchCriteria['firstName'] ); ?>"/>
                            </div></br>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="lastName" value="<?php echo htmlentities( $searchCriteria['lastName'] ); ?>" />
                            </div></br>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" class="form-control" name="email" value="<?php echo htmlentities( $searchCriteria['email'] ); ?>" />
                            </div></br>
                            <input type="submit" value="Search" class="btn btn-default" /></br>
                    </fieldset>
                </form>
                </br>
                <hr>

                <?php
                /**
                 * Iterate through API results
                 *******************************************/
                ?>
                <?php if( isset($result['page']['totalResults'] ) && $result['page']['totalResults'] >= 1 ): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>NENSA #</th>
                            <th>USSA #</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($result['searchResults'] as $r): ?>
                        <tr>
                            <td><?php echo $r['Account ID']; ?> 
                            <td><?php echo $r['First Name']; ?> <?php echo $r['Last Name']; ?></td>
                            <td><?php echo $r['Email 1']; ?></td>
                            <td><?php echo $r['City']; ?> <?php echo $r['State']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p><?php echo $message; ?></p>
                <?php endif; ?>

<?php
}
?>