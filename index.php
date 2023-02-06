<!DOCTYPE HTML>
<html>
    <head>
        <title>Transactions</title>
        <link rel='stylesheet/less' href='resources/css/style.less'>
        <script src='//cdnjs.cloudflare.com/ajax/libs/less.js/3.11.1/less.min.js'></script>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
    </head>
    <body>
        <?php
            $PATH = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

            $transactionsRequest = curl_init($PATH."/process-card-transactions-api/admin/transactions?password=".$_GET['password']);
            curl_setopt($transactionsRequest, CURLOPT_RETURNTRANSFER, true);
            $transactionsResponse = curl_exec($transactionsRequest);
            $transactions = json_decode($transactionsResponse, true);

            $message = '';

            foreach ($transactions as $transaction) {
                $message .=  '<div class="card">';
                $message .=  '<div class="header">';
                $message .= '<b>'.$transaction['merchant'].'</b>';
                $message .= '<div class="amount">$'.number_format($transaction['amount'], 2, '.', '').'</div>';
                $message .= '</div>';

                $buttons = '';

                if ($transaction['bestMatchLocation'] && count($transaction['bestMatchLocation']) > 0) {
                    $message .= '<a href="'.$transaction['bestMatchLocation']['url'].'">';
                    $message .= $transaction['bestMatchLocation']['name'];
                    $message .= '</a>';
                    $message .= ' ('.$transaction['bestMatchLocation']['category'].')';
                    $message .= '<br>';
                    $message .= $transaction['bestMatchLocation']['address'];
                    $message .= '<br><br>';

                    if ($transaction['bestMatchLocation']['lists'] && count($transaction['bestMatchLocation']['lists']) > 0) {
                        $lists = $transaction['bestMatchLocation']['lists'];

                        foreach ($lists as $list) {
                            if ($list['name'] !== 'My Saved Places') {
                                $message .= '&#9989; On '.$list['name'].' list';
                            }
                        }
                    }

                    if ($transaction['bestMatchLocation']['suggestedLists'] && count($transaction['bestMatchLocation']['suggestedLists']) > 0) {
                        $suggestedList = $transaction['bestMatchLocation']['suggestedLists'][0];
                        $buttons .= '<a class="button" href="'.$suggestedList['addToFoursquareList'].'">&#128205;&nbsp;&nbsp;Save</a>';
                    }
                }

                $buttons .= '<a class="button" href="'.$transaction['actions']['addToSplitwise'].'">&#128184;&nbsp;&nbsp;Split</a>';
                $buttons .= '<a class="button" href="'.$transaction['actions']['addToIgnoreList'].'">&#10060;&nbsp;&nbsp;Ignore</a>';
                $message .= '<div class="buttons">'.$buttons.'</div>';
                $message .= '</div>';
            }

            echo '<div id="cards">'.$message.'</div>';
        ?>
        <script src='resources/js/main.js'></script>
    </body>
</html>