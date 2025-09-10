<?php
class Auth {
    public static function register(db $db, string $username, string $password): string {
        //validering
        if (strlen($username) < 3 || strlen($username) > 14) {
            return "brugernavn skal være mellem 3 og 14 tegn";
        }
        if (strlen($password) < 8) {
            return "adgangskode skal være på mindst 8 tegn";
        }

        //check for duplicates
        $exists = $db->sql("SELECT id FROM login WHERE username=:u", [":u" => $username]);
        if ($exists) {
            return "brugernavnet er allerede taget";
        }

        //indsæt ny bruger
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $db->sql(
            "INSERT INTO login (username, hashed_password) VALUES (:u, :h)",
            [":u" => $username, ":h" => $hash],
            false
        );
        return ""; //hvis der ikke er nogen errors
    }
    public static function loginUserSession (int $userId, string $username, string $password): void {
        $_SESSION["id"] = $userId;
        $_SESSION["username"] = $username;
    }
}