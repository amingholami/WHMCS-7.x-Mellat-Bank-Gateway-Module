# WHMCS 7.x Mellat Bank Gateway Module #
## Summary ##

Payment Gateway module of [Mellat Bank](https://www.bankmellat.ir/) allow you to integrate payment solutions with the WHMCS 7.x
platform.

## Install ##
Befor activating this gateway module, run below sql code in your database:

```
 CREATE TABLE IF NOT EXISTS `morders` (
  `oid` int(20) NOT NULL AUTO_INCREMENT,
  `invoiceid` int(20) NOT NULL,
  `amount` double NOT NULL,
  `description` text NOT NULL,
  `isPayed` int(2) NOT NULL,
  `resLink` int(4) NOT NULL,
  `resBank` int(4) NOT NULL,
  `refid` varchar(255) NOT NULL,
  `saleorderid` bigint(30) NOT NULL,
  `refund` int(2) NOT NULL,
  PRIMARY KEY (`oid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
```


## Useful Resources
* [Creator](https://www.itpiran.com/)
* [Mellat Bank](https://www.bankmellat.ir/)