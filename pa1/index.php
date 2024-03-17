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
    <div class="container">
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
    <div class="container">
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
        
            // Form to like a movie
            echo "<form method='post' action=''>";
            echo "<div class='form-group'>";
            echo "<label for='movieId'>Movie ID:</label>";
            echo "<input type='number' class='form-control' name='movieId' id='movieId' required>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='userEmail'>Your Email:</label>";
            echo "<input type='email' class='form-control' name='userEmail' id='userEmail' required>";
            echo "</div>";
            echo "<button type='submit' class='btn btn-primary' name='likeMovie'>Like Movie</button>";
            echo "</form>";

            // Check if view all movies button is clicked
            if(isset($_POST['view_movies'])) {
                $stmt = $conn->prepare("SELECT MotionPicture.id, MotionPicture.name, GROUP_CONCAT(Genre.genre_name) AS genres, MotionPicture.rating, MotionPicture.budget, MotionPicture.production FROM MotionPicture JOIN Genre ON MotionPicture.id = Genre.mpid GROUP BY MotionPicture.id");

                $stmt->execute();
                $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<h2>View All Movies</h2>";
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

                echo "<h2>View All Actors</h2>";
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

</body>
</html>
