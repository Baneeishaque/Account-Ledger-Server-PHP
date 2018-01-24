SET @to_rename_account_id = 17;
SET @old_name="Freecharge DK";
SET @new_name="FreeCharge DK";

-- Child Accoutns Must Be 0
SELECT * FROM `accounts` WHERE `parent_account_id`=@to_rename_account_id;

SELECT * FROM `accounts` WHERE `account_id`=@to_rename_account_id;

UPDATE `accounts` SET `full_name` = REPLACE(`full_name`,@old_name,@new_name),`name` = @new_name WHERE `account_id` = @to_rename_account_id;

SELECT * FROM `accounts` WHERE `account_id`=@to_rename_account_id;




