<?php
/**
 * @var db $db
 */

class Auth {
    public static function register(db $db, string $username, string $password): string {
        //validering
        if (strlen($username) < 3 || strlen($username) > 20) {
            return "brugernavn skal være mellem 3 og 20 tegn";
        }
        if (strlen($password) < 8) {
            return "adgangskode skal være på mindst 8 tegn";
        }

        //check for duplicates
        $exists = $db->sql("SELECT loginId FROM login WHERE loginNavn = :navn LIMIT 1", [":navn" => $username]);
        if ($exists) {
            return "brugernavnet er allerede taget";
        }

        //indsæt ny bruger
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $db->sql(
            "INSERT INTO login (loginNavn, loginKodeord) VALUES (:navn, :hash)",
            [":navn" => $username, ":hash" => $hash],
            false
        );
        return ""; //hvis der ikke er nogen errors
    }

    //læs bruger fra brugernavn i login
    public static function getUserByUsername(db $db, string $username): ?object {
        $rows = $db->sql(
            "SELECT loginId, loginNavn, loginKodeord
                    FROM login
                WHERE loginNavn = :navn
                LIMIT 1",
            [":navn" => $username]
        );
        return $rows [0] ?? null;
    }

    //session start ved login
    public static function loginUserSession(int $userId, string $username): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user_id']  = $userId;
        $_SESSION['username'] = $username;
    }

    public static function logout(): void {
        session_unset();
        session_destroy();
    }
}

//skal det være en public static function?