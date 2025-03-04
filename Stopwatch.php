<?php

class Stopwatch {

    private $mysqli;

    private $stopwatch_id;


    public function __construct(mysqli $mysqli, $stopwatch_id) {
        $this->mysqli = $mysqli;
        $this->stopwatch_id = $stopwatch_id;
    }


    public function start() {
        $timestamp = time();
        $query = "
				INSERT INTO watering (chat_id, timestamp,watering_interval,user_state)
				VALUES ('$this->stopwatch_id', '$timestamp','5','main')
				ON DUPLICATE KEY UPDATE timestamp = '$timestamp'
			";

        return $this->mysqli->query($query);
    }


    public function stop() {
        $query = "
			DELETE FROM watering
			WHERE chat_id = '$this->stopwatch_id'
		";

        return $this->mysqli->query($query);
    }


    public function watered() {

        $timestamp = time();
        $household = $this->getHousehold();
        if ($household != "") {
            $household = $this->getHousehold();
            $query = "
			UPDATE watering SET timestamp='$timestamp'
			WHERE household = '$household';
			";
        } else {
            $query = "
			INSERT INTO watering (chat_id, timestamp)
			VALUES ('$this->stopwatch_id', '$timestamp')
			ON DUPLICATE KEY UPDATE timestamp = '$timestamp'
			";
        }

        return $this->mysqli->query($query);
    }


    function getHousehold() {
        $household = "";
        $query = "
		SELECT household FROM watering
		WHERE chat_id = $this->stopwatch_id
		";

        $result = $this->mysqli->query($query);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $household = $row['household'];
            }
        }

        return $household;
    }


    public function setHousehold($household) {
        $household = $this->mysqli->real_escape_string($household);
        $query = "INSERT INTO watering (chat_id, household) VALUES ('$this->stopwatch_id', '$household')ON DUPLICATE KEY UPDATE household = '$household'";

        return $this->mysqli->query($query);
    }


    public function needsWatering() {

        $wateringIntervalInSeconds = $this->getWateringInterval() * 86400;
        $timestamp = time();
        $lastWatered = $this->lastWatered();

        if ($timestamp > ($lastWatered + $wateringIntervalInSeconds)) {
            return true;
        } else {
            return false;
        }
    }


    public function getWateringInterval() {
        $wateringInterval = "";
        $query = "
		SELECT watering_interval FROM watering
		WHERE chat_id = '$this->stopwatch_id'
		";

        $result = $this->mysqli->query($query);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $wateringInterval = $row['watering_interval'];
            }
        }

        return $wateringInterval;
    }


    public function setWateringInterval($wateringInterval) {
        $wateringInterval = $this->mysqli->real_escape_string($wateringInterval);
        $household = $this->getHousehold();
        if ($household != "") {

            $query = "
			UPDATE watering SET watering_interval='$wateringInterval'
			WHERE household = '$household';
			";
        } else {

            $query = "
			INSERT INTO watering (chat_id, watering_interval)
			VALUES ('$this->stopwatch_id', '$wateringInterval')
			ON DUPLICATE KEY UPDATE watering_interval = '$wateringInterval'
		";
        }

        return $this->mysqli->query($query);
    }


    public function lastWatered() {
        $timestamp = "";
        $household = $this->getHousehold();
        if ($household == "") {
            $query = "
			SELECT timestamp FROM watering
			WHERE chat_id = '$this->stopwatch_id'
		";
        } else {
            $query = "
			SELECT timestamp FROM watering
			WHERE household = '$household'
		";
        }
        $result = $this->mysqli->query($query);
        if (!mysqli_num_rows($result) > 0) {
            return false;
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                $timestamp = $row['timestamp'];
            }
            return $timestamp;
        }
    }


    public function setState($state) {
        if ($this->isNotSubscribed()) {
            $this->start();
        }
        $query = "
		UPDATE watering SET user_state = '$state'
		WHERE chat_id = $this->stopwatch_id
		";

        return $this->mysqli->query($query);
    }


    public function getState() {
        $user_state = "";
        $query = "
		SELECT user_state FROM watering
		WHERE chat_id = $this->stopwatch_id
		";

        $result = $this->mysqli->query($query);
        if (!mysqli_num_rows($result) > 0) {
            return false;
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                $user_state = $row['user_state'];
            }
            return $user_state;
        }
    }


    public function getAllUsers() {
        $query = "
		SELECT * FROM watering
		";
        $users = [];
        $result = $this->mysqli->query($query);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row['chat_id'];
            }
        }

        return $users;
    }


    function isNotSubscribed() {
        $query = "
		SELECT chat_id FROM watering
		WHERE chat_id = '$this->stopwatch_id'
		";

        $result = $this->mysqli->query($query);
        if (mysqli_num_rows($result) > 0) {
            return false;
        } else {
            return true;
        }
    }

}