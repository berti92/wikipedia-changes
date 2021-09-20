<?php
    error_reporting(0);
    $articleTitle = "";
    $articleName = "";
    $per_year_labels = array();
    $per_year_values = array();
    $per_year_month_labels = array();
    $per_year_month_values = array();
    $author_labels = array();
    $author_values = array();
    function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
    if (empty($_GET["wiki_url"]) == false && (startsWith($_GET["wiki_url"], "https://de.wikipedia.org") || startsWith($_GET["wiki_url"], "https://en.wikipedia.org"))) {
        $articleTitle = end(explode("/", $_GET["wiki_url"]));
        $domain = 'https://de.wikipedia.org';
        if(startsWith($_GET["wiki_url"], "https://en.wikipedia.org")) {
            $domain = 'https://en.wikipedia.org';
        }
        $html = file_get_contents($domain.'/w/index.php?title='.$articleTitle.'&offset=&limit=1000000&action=history');
        $doc = DOMDocument::loadHTML($html);
        $xpath = new DOMXpath($doc);
        $articleName = explode("–", $xpath->query("//h1[@id='firstHeading']")->item(0)->textContent)[0];
        $arrpos = -1;
        $lastyear = "";
        foreach ($xpath->query("//a[@class='mw-changeslist-date']") as $element) {
            $textdate = $element->textContent;
            $textdate = str_replace('Mai', 'May.', $textdate);
            $textdate = str_replace('Mär', 'Mar', $textdate);
            $textdate = str_replace('Dez', 'Dec', $textdate);
            $textdate = str_replace('Okt', 'Oct', $textdate);
            if(startsWith($_GET["wiki_url"], "https://en.wikipedia.org")) {
                $datetime = DateTime::createFromFormat('H:i, d F Y', $textdate);
            } else {
                $datetime = DateTime::createFromFormat('H:i, d. F. Y', $textdate);
            }
            if($lastyear != $datetime->format( 'Y' )) {
                $arrpos = $arrpos + 1;
                $per_year_values[$arrpos] = 0;
            }
            $per_year_labels[$arrpos] = $datetime->format( 'Y' );
            $per_year_values[$arrpos] = $per_year_values[$arrpos] + 1;
            $lastyear = $datetime->format( 'Y' );
        }
        $arrpos = -1;
        $lastyearmonth = "";
        foreach ($xpath->query("//a[@class='mw-changeslist-date']") as $element) {
            $textdate = $element->textContent;
            $textdate = str_replace('Mai', 'May.', $textdate);
            $textdate = str_replace('Mär', 'Mar', $textdate);
            $textdate = str_replace('Dez', 'Dec', $textdate);
            $textdate = str_replace('Okt', 'Oct', $textdate);
            if(startsWith($_GET["wiki_url"], "https://en.wikipedia.org")) {
                $datetime = DateTime::createFromFormat('H:i, d F Y', $textdate);
            } else {
                $datetime = DateTime::createFromFormat('H:i, d. F. Y', $textdate);
            }
            if($lastyearmonth != $datetime->format( 'Y-m' )) {
                $arrpos = $arrpos + 1;
                $per_year_month_values[$arrpos] = 0;
            }
            $per_year_month_labels[$arrpos] = $datetime->format( 'Y-m' );
            $per_year_month_values[$arrpos] = $per_year_month_values[$arrpos] + 1;
            $lastyearmonth = $datetime->format( 'Y-m' );
        }
        foreach ($xpath->query("//a[@class='mw-userlink']") as $element) {
            $textauthor = $element->textContent;
            $key = array_search($textauthor, $author_labels);
            if($key == false) {
                array_push($author_labels, $textauthor);
                array_push($author_values, 1);
            } else {
                $author_values[$key] = $author_values[$key] + 1;
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="keywords" content="Wikipedia, changes, analyze">
        <meta name="description" content="Analyze wikipedia article changes">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://gitcdn.link/repo/Chalarangelo/mini.css/master/dist/mini-default.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js" integrity="sha256-bC3LCZCwKeehY6T4fFi9VfOU0gztUa+S4cnkIhVPZ5E=" crossorigin="anonymous"></script>
        <style>
            .chart-container {
                height: 400px;
                width: 100%;
            }
            .chart-container-hor {
                width: 100%;
            }
        </style>
        <title>Analyze the number of changes from a wikipedia article <?php echo(($articleName != "" ? " - ".$articleName : "")) ?></title>

        <!-- Cookie Consent by https://www.FreePrivacyPolicy.com -->
        <script type="text/javascript" src="//www.freeprivacypolicy.com/public/cookie-consent/4.0.0/cookie-consent.js" charset="UTF-8"></script>
        <script type="text/javascript" charset="UTF-8">
        document.addEventListener('DOMContentLoaded', function () {
        cookieconsent.run({"notice_banner_type":"headline","consent_type":"express","palette":"light","language":"en","page_load_consent_levels":["strictly-necessary"],"notice_banner_reject_button_hide":false,"preferences_center_close_button_hide":false,"website_name":"wikipedia-changes.com","website_privacy_policy_url":"https://www.devbert.de/index.php/en/privacy-notice/"});
        });
        </script>

        <noscript>Cookie Consent by <a href="https://www.FreePrivacyPolicy.com/free-cookie-consent/" rel="nofollow noopener">FreePrivacyPolicy.com</a></noscript>
        <!-- End Cookie Consent -->

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script type="text/plain" cookie-consent="tracking" async src="https://www.googletagmanager.com/gtag/js?id=G-K0V5SYERGN"></script>
        <script type="text/plain" cookie-consent="tracking">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-K0V5SYERGN');
        </script>
    </head>
    <body>
        <h1>Show changes from a wikipedia article</h1>
        <p>Paste in the box below the url of a wikipedia article and then click on "Submit". You will be redirected to a page which will show you the number of changes within charts. Currently only german and english articles are possible!</p>
        <form action="/index.php" method="GET">
            <input type="text" name="wiki_url" value="<?php echo((empty($_GET["wiki_url"]) ? '' : $_GET["wiki_url"])) ?>" />
            <input type="submit" value="Submit" />
        </form>
        <?php if(empty($_GET["wiki_url"]) == false && (startsWith($_GET["wiki_url"], "https://de.wikipedia.org") || startsWith($_GET["wiki_url"], "https://en.wikipedia.org"))) { ?>
            <hr>
            <h2>Changes of the article <?php echo($articleName) ?></h2>
            <h3>Per year</h3>
            <div class="chart-container">
                <canvas id="changes_per_year"></canvas>
            </div>
            <script>
            var ctx1 = document.getElementById('changes_per_year').getContext('2d');
            var myChart1 = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?php echo(json_encode($per_year_labels)) ?>,
                    datasets: [{
                        label: '# of changes',
                        data: <?php echo(json_encode($per_year_values)) ?>,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgb(255, 99, 132)'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            </script>
            <h3>Per year and month</h3>
            <div class="chart-container-hor" style="height: <?php echo(100 + (count($per_year_month_labels) * 30)) ?>px;">
                <canvas id="changes_per_year_month"></canvas>
            </div>
            <script>
            var ctx2 = document.getElementById('changes_per_year_month').getContext('2d');
            var myChart2 = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: <?php echo(json_encode($per_year_month_labels)) ?>,
                    datasets: [{
                        label: '# of changes',
                        data: <?php echo(json_encode($per_year_month_values)) ?>,
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgb(255, 159, 64)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    elements: {
                        bar: {
                            borderWidth: 2,
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            </script>
            <h3>Per author</h3>
            <div class="chart-container-hor" style="height: <?php echo(100 + (count($author_labels) * 30)) ?>px;">
                <canvas id="changes_per_author"></canvas>
            </div>
            <script>
            var ctx3 = document.getElementById('changes_per_author').getContext('2d');
            var myChart3 = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: <?php echo(json_encode($author_labels)) ?>,
                    datasets: [{
                        label: '# of changes',
                        data: <?php echo(json_encode($author_values)) ?>,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgb(54, 162, 235)'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    elements: {
                        bar: {
                            borderWidth: 2,
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            </script>
        <?php } else { ?>
            <h4 style="color: red;">Please note that only german and english articles are possible, your url must start with "https://de.wikipedia.org" or "https://en.wikipedia.org".</h4>
        <?php } ?>
        <footer>
            <a href="https://www.devbert.de/index.php/en/home/">Made with ❤ by Devbert</a>&nbsp;|&nbsp;
            <a href="https://www.devbert.de/index.php/en/privacy-notice/">Privacy notice</a>&nbsp;|&nbsp;
            <a href="#" id="open_preferences_center">Change your cookie preferences</a>
        </footer>
    </body>
</html> 