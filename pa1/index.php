<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- Bootstrap JS dependencies -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSI 127b</title>
</head>
<body>
    <div class="container">
        <h1 style="text-align:center">COSI 127b</h1><br>
        <h3 style="text-align:center">Connecting Front-End to MySQL DB</h3><br>
    </div>
    <div id="ageLimit" class="container">
        <form id="ageLimitForm" method="post" action="index.php">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Enter minimum age" name="inputAge" id="inputAge">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" name="submitted" id="button-addon2">Query</button>
                </div>
            </div>
        </form>
    </div>
    <div class="container">
        <h1>Guests</h1>
        <?php
        // we want to check if the submit button has been clicked (in our case, it is named Query)
        if(isset($_POST['submitted']))
        {
            // set age limit to whatever input we get
            // ideally, we should do more validation to check for numbers, etc. 
           $ageLimit = $_POST["inputAge"]; 
        }
        else
        {
            // if the button was not clicked, we can simply set age limit to 0 
            // in this case, we will return everything
            $ageLimit = 0;
        }

        // we will now create a table from PHP side 
        echo "<table class='table table-md table-bordered'>";
        echo "<thead class='thead-dark' style='text-align: center'>";

        // initialize table headers
        // YOU WILL NEED TO CHANGE THIS DEPENDING ON TABLE YOU QUERY OR THE COLUMNS YOU RETURN
         echo "<tr><th class='col-md-2'>Firstname</th><th class='col-md-2'>Lastname</th></tr></thead>";

        // generic table builder. It will automatically build table data rows irrespective of result
        class TableRows extends RecursiveIteratorIterator {
            function __construct($it) {
                parent::__construct($it, self::LEAVES_ONLY);
            }

            function current() {
                return "<td style='text-align:center'>" . parent::current(). "</td>";
            }

            function beginChildren() {
                echo "<tr>";
            }

            function endChildren() {
                echo "</tr>" . "\n";
            }
        }

        // SQL CONNECTIONS
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            // We will use PDO to connect to MySQL DB. This part need not be 
            // replicated if we are having multiple queries. 
            // initialize connection and set attributes for errors/exceptions
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // prepare statement for executions. This part needs to change for every query
            $stmt = $conn->prepare("SELECT first_name, last_name FROM guests where age>=$ageLimit");

            // execute statement
            $stmt->execute();

            // set the resulting array to associative. 
            $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

            // for each row that we fetched, use the iterator to build a table row on front-end
            foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                echo $v;
            }
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        echo "</table>";
        // destroy our connection
        $conn = null;
    
    ?>

    <div class="container">
            <h1 style="text-align:center">COSI 127b</h1><br>
            <h3 style="text-align:center">Movie Database</h3><br>
    </div>
    

    <div id="likeMovieFormContainer" class="container">
        <?php
        // MySQL Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (isset($_POST['likeMovie'])) {
                $userEmail = $_POST['userEmail']; // The email address entered by the user
                $movieId = $_POST['movieId']; // The ID of the movie the user likes

                // Prepare the insert statement to record the like
                $stmt = $conn->prepare("INSERT INTO Likes (uemail, mpid) VALUES (:userEmail, :movieId)");
                $stmt->bindParam(':userEmail', $userEmail);
                $stmt->bindParam(':movieId', $movieId);

                try {
                    $stmt->execute();
                    echo "<p>Like recorded successfully!</p>";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        // Handle duplicate entry error, if a user has already liked the same movie
                        echo "<p>The user has already liked this movie.</p>";
                    } else {
                        // Handle any other database errors
                        echo "<p>Error: " . $e->getMessage() . "</p>";
                    }
                }
            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $conn = null;
        ?>
    
    <!-- Form to like a movie -->
    <form method='post' action=''>
        <div class='form-group'>
            <label for='movieId'>Movie ID:</label>
            <input type='number' class='form-control' name='movieId' id='movieId' required>
        </div>
        <div class='form-group'>
            <label for='userEmail'>Your Email:</label>
            <input type='email' class='form-control' name='userEmail' id='userEmail' required>
        </div>
        <div class="text-right">
            <button type='submit' class='btn btn-primary' name='likeMovie'>Like Movie</button>
        </div>
    </form>
    </div>
    
    <div id="viewOptionsContainer" class="container mb-4">
        <div class="btn-group" role="group" aria-label="View Options">
            <!-- Button to view all movies -->
            <form method="post" action="index.php">
                <button class="btn btn-primary" type="submit" name="view_movies">View All Movies</button>
            </form>

            <!-- Button to view all actors -->
            <form method="post" action="index.php">
                <button class="btn btn-primary" type="submit" name="view_actors">View All Actors</button>
            </form>
        </div>
    </div>
    <div class="container">
        <?php
        // MySQL Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if view all movies button is clicked
            if(isset($_POST['view_movies'])) {
                $stmt = $conn->prepare("SELECT MotionPicture.id, MotionPicture.name, GROUP_CONCAT(Genre.genre_name) AS genres, MotionPicture.rating, MotionPicture.budget, MotionPicture.production FROM MotionPicture JOIN Genre ON MotionPicture.id = Genre.mpid GROUP BY MotionPicture.id");

                $stmt->execute();
                $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<h3>View All Movies</h3>";
                echo "<table class='table'>";
                echo "<thead class='bg-dark text-white'><tr><th>ID</th><th>Name</th><th>Genre</th><th>Rating</th><th>Budget</th><th>Production</th></tr></thead>";
                echo "<tbody>";
                foreach ($movies as $movie) {
                    echo "<tr>";
                    echo "<td>{$movie['id']}</td>";
                    echo "<td>{$movie['name']}</td>";
                    echo "<td>{$movie['genres']}</td>";
                    echo "<td>{$movie['rating']}</td>";
                    echo "<td>{$movie['budget']}</td>";
                    echo "<td>{$movie['production']}</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }

            // Check if view all actors button is clicked
            elseif(isset($_POST['view_actors'])) {
                $stmt = $conn->prepare("SELECT id, name, nationality, gender FROM people");

                $stmt->execute();
                $actors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<h3>View All Actors</h3>";
                echo "<table class='table'>";
                echo "<thead class='bg-dark text-white'><tr><th>Name</th><th>Gender</th><th>Nationality</th></tr></thead>";
                echo "<tbody>";
                foreach ($actors as $actor) {
                    echo "<tr>";
                    echo "<td>{$actor['name']}</td>";
                    echo "<td>{$actor['gender']}</td>";
                    echo "<td>{$actor['nationality']}</td>";
                    echo "</tr>";
                    
                }
                echo "</tbody>";
                echo "</table>";
            }
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        $conn = null;
        ?>
    </div>

    <hr class="round" style="border-top: 5px solid #000; border-radius: 5px;">

    <div id="searchMovie" class="container">
        <!-- Form to search for a movie -->
        <form method='post' action='index.php'>
            <div class='form-group'>
                <label for='movieName'>Search For A Movie</label>
                <input type='text' class='form-control' name='movie' id='movieName' required placeholder="Movie name...">
            </div>
            <div class="text-right">
                <button type='submit' class='btn btn-primary' name='searchMovie'>Search</button>
            </div>
        </form>

        <?php
            // MySQL Connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "COSI127b";

            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                if (isset($_POST['searchMovie'])) {
                    // Get the movie name from the form
                    $searchMovieName = $_POST['movie'];

                    // Prepare SQL query with parameterized movie name
                    $stmt = $conn->prepare("SELECT name, rating, production, budget FROM MotionPicture WHERE name = :searchMovieName");
                    $stmt->bindParam(':searchMovieName', $searchMovieName);

                    // Execute the query
                    $stmt->execute();

                    // Fetch the results
                    $movieDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Display the results in a table
                    echo "<h3>Search Results</h3>";
                    echo "<table class='table'>";
                    echo "<thead><tr><th>Movie Name</th><th>Rating</th><th>Production</th><th>Budget</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($movieDetails as $movie) {
                        echo "<tr>";
                        echo "<td>{$movie['name']}</td>";
                        echo "<td>{$movie['rating']}</td>";
                        echo "<td>{$movie['production']}</td>";
                        echo "<td>{$movie['budget']}</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                }
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            $conn = null;
            ?>
    </div>

    <hr class="round" style="border-top: 5px solid #000; border-radius: 5px;">

    <div id="searchLiked" class="container">
        <!-- Form to search for a movie -->
        <form method='post' action='index.php'>
            <div class='form-group'>
                <label for='userEmail'>Search for liked movies</label>
                <input type='email' class='form-control' name='userEmail' id='userEmail' required placeholder="Email address...">
            </div>
            <div class="text-right">
                <button type='submit' class='btn btn-primary' name='searchLiked'>Search</button>
            </div>
        </form>

        <?php
        // MySQL Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (isset($_POST['searchLiked'])) {
                // Get the user's email from the form
                $userEmail = $_POST['userEmail'];

                // Prepare SQL query with parameterized user's email
                $stmt = $conn->prepare("SELECT name FROM User WHERE email = :userEmail");
                $stmt->bindParam(':userEmail', $userEmail);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                

                //Check if user exists in the system
                if ($userData) {
                    $username = $userData['name'];
                    $stmt = $conn->prepare("SELECT MotionPicture.name, MotionPicture.rating, MotionPicture.production, MotionPicture.budget FROM MotionPicture INNER JOIN Likes ON MotionPicture.id = Likes.mpid WHERE Likes.uemail = :userEmail");
                    $stmt->bindParam(':userEmail', $userEmail);
                    $stmt->execute();
                    $likedMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Execute the query
                    $stmt->execute();

                    // Fetch the results
                    $likedMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($likedMovies) {
                        echo "<h3>{$username}'s Liked Movies</h3>";
                        echo "<table class='table'>";
                        echo "<thead><tr><th>Movie Name</th><th>Rating</th><th>Production</th><th>Budget</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($likedMovies as $movie) {
                            echo "<tr>";
                            echo "<td>{$movie['name']}</td>";
                            echo "<td>{$movie['rating']}</td>";
                            echo "<td>{$movie['production']}</td>";
                            echo "<td>{$movie['budget']}</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>You haven't liked a movie yet.</p>";
                    }
                } else {
                    echo "<p>User not found &#128542;. Please check your email address. </p>";
                }
            }
        } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
        }
        $conn = null;
        ?>
    </div> 

    <hr class="round" style="border-top: 5px solid #000; border-radius: 5px;">

    <div id="searchByCountry" class="container">
        <!-- Form to search for a movie -->
        <form method='post' action='index.php'>
            <div class='form-group'>
                <label for='country'>Search Movies By Shooting Country</label>
                <input type='country' class='form-control' name='country' id='country' required placeholder="Where...">
            </div>
            <div class="text-right">
                <button type='submit' class='btn btn-primary' name='searchByCountry'>Search</button>
            </div>
        </form>

        <?php
        // MySQL Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (isset($_POST['searchByCountry'])) {
                // Get the user's email from the form
                $countryName = $_POST['country'];

                // Prepare SQL query with parameterized user's email
                $stmt = $conn->prepare("SELECT country FROM Location WHERE country = :countryName");
                $stmt->bindParam(':countryName', $countryName);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                

                //Check if location exists in the system
                if ($userData) {
                    $stmt = $conn->prepare("SELECT DISTINCT MotionPicture.name FROM MotionPicture INNER JOIN Location ON MotionPicture.id = Location.mpid WHERE Location.country = :countryName");
                    $stmt->bindParam(':countryName', $countryName);
                    $stmt->execute();
                    $likedMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Execute the query
                    $stmt->execute();

                    // Fetch the results
                    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($locations) {
                        echo "<h3>Movies that are filmed at {$countryName}</h3>";
                        echo "<table class='table'>";
                        echo "<thead><tr><th>Movie Name</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($locations as $movie) {
                            echo "<tr>";
                            echo "<td>{$movie['name']}</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>Nothing has been filmed here before.</p>";
                    }
                } else {
                    echo "<p>We don't have record for this location yet. But wait for it...&#x2708;&#x1F378;</p>";
                }
            }
        } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
        }
        $conn = null;
        ?>
    </div> 

    <hr class="round" style="border-top: 5px solid #000; border-radius: 5px;">

    <div id="zipCodeDirector" class="container">
        <!-- Form to search for a movie -->
        <form method='post' action='index.php'>
            <div class='form-group'>
                <label for='zip'>Search For TV Directors By Zip Code</label>
                <input type='text' class='form-control' name='zip' id='zip' required placeholder="Zip Code Number...">
            </div>
            <div class="text-right">
                <button type='submit' class='btn btn-primary' name='zipCodeDirector'>Search</button>
            </div>
        </form>

        <?php
        // MySQL Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "COSI127b";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (isset($_POST['zipCodeDirector'])) {
                $zipCodeInput = $_POST['zip']; // Store user input zip code
            
                $stmt = $conn->prepare("SELECT zip FROM Location WHERE zip = :zipCode");
                $stmt->bindParam(':zipCode', $zipCodeInput);
                $stmt->execute();
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
                //Check if zip code exists in the system
                if ($userData) {
                    $stmt = $conn->prepare("SELECT DISTINCT People.name AS director_name, TVSeries.name AS series_name 
                        FROM (
                            SELECT *
                            FROM Role
                            WHERE Role.role_name = 'Director'
                        ) AS DirectorRoles
                        INNER JOIN People ON DirectorRoles.pid = People.id
                        INNER JOIN (
                            SELECT MotionPicture.id, MotionPicture.name
                            FROM MotionPicture
                            INNER JOIN Series ON MotionPicture.id = Series.mpid
                            INNER JOIN Location ON MotionPicture.id = Location.mpid
                            WHERE Location.zip = :zipCode
                        ) AS TVSeries ON DirectorRoles.mpid = TVSeries.id
                        ");
                    $stmt->bindParam(':zipCode', $zipCodeInput);
                    $stmt->execute();
                    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                    // Output the results
                    if ($locations) {
                        echo "<h3>Directors had worked at $zipCodeInput</h3>";
                        echo "<table class='table'>";
                        echo "<thead><tr><th>Director Name</th><th>TV Name</th></tr></thead>";
                        echo "<tbody>";
                        foreach ($locations as $movie) {
                            echo "<tr>";
                            echo "<td>{$movie['director_name']}</td>"; 
                            echo "<td>{$movie['series_name']}</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                    } else {
                        echo "<p>No one had worked here yet.</p>";
                    }
                } else {
                    echo "<p>We don't have record for this zip code yet. But wait for it...&#x2708;&#x1F378;</p>";
                }
            }
        } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
        }
        $conn = null;
        ?>
    </div> 

    <hr class="round" style="border-top: 5px solid #000; border-radius: 5px;">
    
</body>
</html>
