<?php 
  class Word {
    // DB stuff
    private $conn;
    private $table = 'words';

    // Post Properties
    public $id;
    public $word;
    public $definition;

    // Constructor with DB
    public function __construct($db) {
      $this->conn = $db;
    }


    // Get Single Word
    public function read_single() {
       // Create query
       $query = 'SELECT 
       w.id,
       w.word,
       w.definition
      FROM
        ' . $this->table . ' p
      WHERE
        p.id = ?
      LIMIT 0,1';

      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Bind ID
      $stmt->bindParam(1, $this->id);

      // Execute query
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Set properties
      $this->word = $row['word'];
      $this->definition = $row['definition'];
    }

    // Create Word Entry
    public function create() {
      // Create query
      $query = 'INSERT INTO ' . 
          $this->table . '
        SET
          word = :word';
          
      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Clean data
      $this->word = htmlspecialchars(strip_tags($this->word));

      // Bind data
      $stmt->bindParam(':word', $this->word);

      // Execute query
      if($stmt->execute()) {
        return true;
      }

      // Print error if something goes wrong
      printf("Error: %s.\n", $stmt->error);

      return false;
    }

    // Update Word Entry
    public function update() {
      // Create query
      $query = 'UPDATE ' . 
          $this->table . '
        SET
          word = :word,
          definition = :definition,
        WHERE
          word = :word';

      // Prepare statement
      $stmt = $this->conn->prepare($query);

      // Clean data
      $this->word = htmlspecialchars(strip_tags($this->word));
      $this->definition = htmlspecialchars(strip_tags($this->definition));

      // Bind data
      $stmt->bindParam(':word', $this->word);
      $stmt->bindParam(':definition', $this->definition);

      // Execute query
      if($stmt->execute()) {
        return true;
      }

      // Print error if something goes wrong
      printf("Error: %s.\n", $stmt->error);

      return false;
    }

    
  }