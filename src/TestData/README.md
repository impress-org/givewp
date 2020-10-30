# Test Data for GiveWP

## A note on performance

The generating and seeding of donors and donations is intended to be a step process, meaning that each item is created in turn as opposed to mass insert queries. This is largely due to the relational nature of the MySQL database and the meta table architecture which requires multiple insert queries per item to maintain the relationship between the inserted item row and the related meta rows.

Such an application as generating data is well suited for a command line environment, in which long running processes are not hindered by the request processing time or the performance expectations of a production application.
