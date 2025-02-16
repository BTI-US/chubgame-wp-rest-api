## ChubGame WP REST API

## Game Logic

### Parent User Sequence Diagram

```mermaid
sequenceDiagram
    participant Parent as Parent User
    participant WP as WordPress Database
    participant Dice as Dice Game Logic

    Parent->>WP: Register with Promotion Code
    WP->>Dice: Store Promotion Code as Parent
    Dice->>WP: Save Parent User and Chips Info
    Parent->>Dice: Trigger Dice Game (Win/Loss)
    Dice->>WP: Update Parent Points (Win/Loss)
    Dice->>WP: Check Promotion Code for Validity
    WP->>Dice: Validate Promotion Code
    Dice->>Parent: Notify Promotion Code Valid
    Dice->>Parent: Adjust Points and Chips Based on Game Result
```

Child User Sequence Diagram:

```mermaid
sequenceDiagram
    participant Child as Child User
    participant WP as WordPress Database
    participant Dice as Dice Game Logic

    Child->>WP: Send Promotion Code and Username
    WP->>Dice: Validate Promotion Code
    Dice->>WP: Check Promotion Code Validity
    WP->>Dice: Return Promotion Code Validity
    Dice->>Child: Notify Promotion Code Validity
    Child->>Dice: Trigger Dice Game (Win/Loss)
    Dice->>WP: Update Child Points (Win/Loss)
    Dice->>Child: Adjust Points and Chips Based on Game Result
```

PvE Single Player Sequence Diagram

```mermaid
sequenceDiagram
    participant Player as Single Player
    participant WP as WordPress Database
    participant Dice as Dice Game Logic

    Player->>Dice: Start PvE Game
    Dice->>Dice: Generate Random Win/Loss
    alt Player Wins
        Dice->>WP: Add Double Chips to Player Balance
        Dice->>Player: Notify Win and Update Balance
    else Player Loses
        Dice->>WP: Deduct Chips from Player Balance
        Dice->>Player: Notify Loss and Update Balance
    end
```

## WordPress API Endpoints

### Flowchart for the Validate Promotion Code

Promotion Code Verification for Child User:

```mermaid
sequenceDiagram
    participant User
    participant API
    participant Database

    User->>API: Send POST /wp-json/chubgame/v1/validate <br> with promotionCode and username
    API->>Database: Query dice_data table for promotionCode and is_promotion_user = 1
    Database->>API: Return promotion code record (valid or not)
    alt Promotion code is valid
        API->>Database: Check if promotion code has already been used
        Database->>API: Return usage status
        alt Promotion code not used
            API->>Database: Associate parent and child users
            API->>User: Return response with valid status and parent dice amount
        else Promotion code used
            API->>User: Return error response (promotion code already used)
        end
    else Promotion code invalid
        API->>User: Return error response (invalid promotion code)
    end
```

### Flowchart for the Send Dice Data

Parent User Sequence Diagram:

```mermaid
sequenceDiagram
    participant Parent as Parent User
    participant API
    participant Database

    Parent->>API: Send POST /wp-json/chubgame/v1/send <br> with diceAmount, totalPoints, promotionCode, isPromotionUser, username, chips
    API->>Database: Validate user and parameters
    alt Parameters valid
        alt Promotion code is empty and isPromotionUser is false (PvE mode)
            API->>API: Generate random win/loss
            alt User wins
                API->>Database: Add double chips to user balance
                API->>Database: Log dice data for win
                API->>Parent: Return success response with updated balance and result
            else User loses
                API->>Database: Deduct chips from user balance
                API->>Database: Log dice data for loss
                API->>Parent: Return success response with updated balance and result
            end
        else Promotion code is not empty and isPromotionUser is true (PvP mode)
            API->>Database: Generate promotion code if empty
            API->>Database: Check if promotion code has already been used
            alt Promotion code not used
                API->>Database: Deduct chips from parent user
                API->>Database: Log dice data for parent
                API->>Parent: Return success response with updated balance and promotion code
            else Promotion code used
                API->>Parent: Return error response (promotion code already used)
            end
        end
    else Parameters invalid
        API->>Parent: Return error response (missing or invalid parameters)
    end
```

Child User Sequence Diagram:

```mermaid
sequenceDiagram
    participant Child as Child User
    participant API
    participant Database

    Child->>API: Send POST /wp-json/chubgame/v1/send <br> with diceAmount, totalPoints, promotionCode, isPromotionUser, username, chips
    API->>Database: Validate user and parameters
    alt Parameters valid
        API->>Database: Find parent by promotion code
        alt Parent found
            API->>Database: Check child balance
            alt Child balance sufficient
                API->>Database: Deduct chips from child user
                API->>Database: Determine winner and distribute chips
                API->>Database: Log dice data for child and parent
                API->>Child: Return success response with updated balance and result
            else Child balance insufficient
                API->>Database: Refund parent
                API->>Child: Return error response (insufficient balance)
            end
        else Parent not found
            API->>Child: Return error response (invalid promotion code or parent not found)
        end
    else Parameters invalid
        API->>Child: Return error response (missing or invalid parameters)
    end
```

PvE Single Player Sequence Diagram:

```mermaid
sequenceDiagram
    participant Player as Single Player
    participant API
    participant Database

    Player->>API: Send POST /wp-json/chubgame/v1/send <br> with diceAmount, totalPoints, promotionCode (empty), isPromotionUser (false), username, chips
    API->>Database: Validate user and parameters
    alt Parameters valid
        API->>API: Generate random win/loss
        alt Player wins
            API->>Database: Add double chips to player balance
            API->>Database: Log dice data for win
            API->>Player: Return success response with updated balance and result
        else Player loses
            API->>Database: Deduct chips from player balance
            API->>Database: Log dice data for loss
            API->>Player: Return success response with updated balance and result
        end
    else Parameters invalid
        API->>Player: Return error response (missing or invalid parameters)
    end
```

## Promotion Code Validation API

Validates a promotion code and associates the parent user with the promotion code.

### Endpoint

`POST /wp-json/chubgame/v1/validate`

### Parameters

- `promotionCode` (string): The promotion code to validate.
- `username` (string): The username of the child user.

### Response

#### Success

```json
{
    "code": 200,
    "message": "Promotion code is valid and successfully applied.",
    "data": {
        "status": "success",
        "valid": true,
        "parent_user_id": 123,
        "parent_dice_amount": 5
    }
}
```

#### Error

```json
{
    "code": 400,
    "message": "Invalid promotion code",
    "data": {
        "status": "invalid_promotion_code"
    }
}
```

## Balance Validation API

Checks if the user has sufficient balance for the specified chips.

### Endpoint

`POST /wp-json/chubgame/v1/check-balance`

### Parameters

- `username` (string): The username of the user.
- `chips` (int): The number of chips to check.

### Response

#### Success

```json
{
    "code": 200,
    "message": "Balance is sufficient for current user",
    "data": {
        "status": "success",
        "balance": 1000
    }
}
```

#### Error

```json
{
    "code": 400,
    "message": "Insufficient balance for parent user",
    "data": {
        "status": "insufficient_balance",
        "balance": 500
    }
}
```

## Dice Data and Manage Chips API

Handles the dice game data and manages the chips for parent and child users.

### Endpoint

`POST /wp-json/chubgame/v1/send`

### Parameters

- `diceAmount` (int): The amount of dice rolled.
- `totalPoints` (int): The total points scored.
- `promotionCode` (string): Optional: The promotion code used, if empty, then the user is in the PvE mode.
- `isPromotionUser` (bool): Indicates if the user is a promotion user.
- `username` (string): The username of the user.
- `chips` (int): The number of chips of the current user.

### Response

#### Success

```json
{
    "code": 200,
    "message": "Game processed successfully",
    "data": {
        "status": "success",
        "balance": 1000,
        "result": 100,
        "promotion_code": "DEBUGCODE1234567"
    }
}
```

#### Error

```json
{
    "code": 400,
    "message": "This promotion code has already been used",
    "data": {
        "status": "promotion_used"
    }
}
```

## FAQ

1. Using Nginx as a reverse proxy for the WordPress REST API cause the error `404 Not Found`

    - **Solution**: Add the following configuration to the Nginx configuration file:

    ```nginx
        listen 80;
        server_name 127.0.0.1;
        index index.html index.htm index.php;
        root  /www/wwwroot/127_0_0_1;
        location / {
            try_files $uri $uri/ /index.php?$args;
        }
    ```
