<?php
//a file which contains names of db columns or other common vars

define("EMPTY_NUM_VALUE",0);
define("EMPTY_STRING_VALUE","#novalue#");

define("DBV_DBCON","dbcon");
define("DBV_TRANSACTION","transactionstatus");
define("DBV_DBOO","dboo");

define("ERRID","ERR".time());

define("RESULT_STATUS","status");
define("RESULT_PAYLOAD","payload");
define("ERRPAYLOAD_MESSAGE","error");

define("MAX_RECORDS_ALLOWED",100);

define("CB_LIVE_STATUS",1007);//to be used as status in database(_status)
define("CB_CLOSEDLIVE_STATUS",7001);
define("CB_CLOSEDAUTO_STATUS",1177);
define("CB_CLOSED_RES",8178);
define("CB_ALL_RES",8888);
define("CB_NOSTATUS_RES",1001);
define("CB_FORCBID_RES",6161);
define("CB_FORCBID_UPDATE_EXPECTEDDURATION",6171);
define("CB_WALKIN_STATSCOUNT",6181);
define("CB_WALKIN_HISTORY",6191);
define("CB_RESERVATIONS_HISTORY",6201);

define("CB_STATUSFORCBID_RES",6281);
define("CB_CHECKCBID_FORCBID_RES",6291);
define("CB_CLOSETABLES_FORCBID_RES",2177);


define("CB_FAILED_TABLEBOOKING",-66);
define("CB_NO_WAITID", -1);
define("CB_NO_ADVANCEID", -1);
define("CB_LOOKAHEADTIME_HOUR",2);

define("RT_ACTIVESTATUS", 1456);
define("RT_DISABLEDSTATUS", 6541);
define("RT_OLSTATUS_AVAILABLEONLINE", 2345);
define("RT_OLSTATUS_UNAVAILABLEONLINE", 5432);

define("NOEND_TIMESTAMP", "2012-01-01 01:01:01");
define("DEFAULT_ENDTIME", "23:59:59");

define("WL_LIVE_STATUS",2269);
define("WL_CONVTOCURRENT_STATUS",2279);//closed
define("WL_NOTCONVTOCURRENT_STATUS",2289);//closed
define("WL_AUTOCLOSED_STATUS",2299);//closed
define("WL_CLOSED_LIST",2300);//represents any closed
define("WL_ALL_LIST",2400);//represents any closed

define("WL_FORWAITID_RES",4010);
define("WL_CHECKWAITID_FORWAITID_RES",4020);
define("WL_STATUSFORWAITID_RES",4030);

define("AB_LOOKAHEADTIME",2);
define("AB_AUTONLINE_BYUSER","AutoOnline");
define("AB_NOAUTO_BYUSER","NOAUTO");

define("AB_CALL_BOOKINGMETHODTYPE","Call");
define("AB_TRSONLINE_BOOKINGMETHODTYPE","TRS Online");
define("AB_INPERSON_BOOKINGMETHODTYPE","In Person");
define("AB_OWNONLINE_BOOKINGMETHODTYPE","Own Online");
define("AB_THIRDPARTY_BOOKINGMETHODTYPE","Thirdparty");
define("AB_UNKNOWN_BOOKINGMETHODTYPE","Unkown");

define("AB_FORABID_RES",2010);
define("AB_FORDATETIME_FORABID_RES",2020);
define("AB_STATUS_FORABID_RES",2030);
define("AB_CHECKABID_FORABID_RES",2040);

define("AB_UNKNOWN_STATUS",2111);//tentative..currently not used
define("AB_REQUESTED_STATUS",2222);//tentative..currently not used
define("AB_APPROVED_STATUS",2333);//tentative
define("AB_CALLETC_NOTCONFIRMED_STATUS",2555);//tentative
define("AB_CALLETC_CONFIRMED_STATUS",2444);//Confirmed
define("AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUS",2666);//CLOSED SUCCESSS
define("AB_USERCONFIRMED_AND_NOSHOW_STATUS",2777);//FAIL NOSHOW
define("AB_NOTCONFIRMED_CANCELLED_STATUS",2888);//FAIL NOSHOW
define("AB_CONFIRMED_CONVERTEDCURRENT_STATUS",2999);//CoNVERTED success
define("AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUS",3222);//CONVERTED success

define("AB_REQUESTED_STATUSTYPE","Requested");
define("AB_UNKNOWN_STATUSTYPE","Unknown");
define("AB_APPROVED_STATUSTYPE","Approved");
define("AB_CALLETC_CONFIRMED_STATUSTYPE","Confirmed");
define("AB_CALLETC_NOTCONFIRMED_STATUSTYPE","Not Confirmed");
define("AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUSTYPE","User Cancelled");//FAIL
define("AB_USERCONFIRMED_AND_NOSHOW_STATUSTYPE","No Show");//FAIL
define("AB_NOTCONFIRMED_CANCELLED_STATUSTYPE","No Show");//FAIL
define("AB_CONFIRMED_CONVERTEDCURRENT_STATUSTYPE","Served");
define("AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUSTYPE","Served");

define("AB_TENTATIVE_RES",444);
define("AB_TENTATIVE_RESARRAY",serialize(array(AB_APPROVED_STATUS,AB_CALLETC_NOTCONFIRMED_STATUS)));

define("AB_CONFIRMED_RES",464);
define("AB_GUESTCONFIRMED_RESARRAY",serialize(array(AB_CALLETC_CONFIRMED_STATUS)));

define("AB_USERCANCELLED_RES",555);
define("AB_USERCANCELLED_RESARRAY",serialize(array(AB_USERCONFIRMED_USERCALLED_ANDCANCELLED_STATUS)));

define("AB_NOSHOW_RES",666);
define("AB_NOSHOW_RESARRAY",serialize(array(AB_USERCONFIRMED_AND_NOSHOW_STATUS,AB_NOTCONFIRMED_CANCELLED_STATUS)));

define("AB_CONVERTEDCURRENT_RES",777);
define("AB_CONVERTEDCURRENT_RESARRAY",serialize(array(AB_CONFIRMED_CONVERTEDCURRENT_STATUS,AB_NOTCONFIRMED_CONVERTEDCURRENT_STATUS)));

define("AB_ALL_RES",888);
define("AB_ALLCANCELLEDORNOSHOW_RES",999);//merge AB_NOSHOW_RESARRAY,AB_USERCANCELLED_RESARRAY

define("AB_ASSIGNED_TABLE_RES",1999);


define("AB_ASSIGNTABLE_MODE","Assign");
define("AB_REMOVETABLE_MODE","Remove");

define("AB_PENDING_TABLESTATUS",6222);//not used
define("AB_ASSIGNED_TABLESTATUS",6333);//not used
define("AB_CONVERTEDCURRENT_TABLESTATUS",6444);//not used
define("AB_CANCELLORCLOSE_TABLESTATUS",6555);//not used

define("AB_PENDING_TABLESTATUSTYPE","Pending");
define("AB_ASSIGNED_TABLESTATUSTYPE","Assigned");
define("AB_CONVERTEDCURRENT_TABLESTATUSTYPE","CC");
define("AB_CANCELLORCLOSE_TABLESTATUSTYPE","CanClo");

define("RRT_DEFAULT_MINCAPACITY","2");
define("RRT_DEFAULT_MAXCAPACITY","4");


//*guests
define("CHECKGUID_FORGUID",-4010);
define("CHECK_CONTACTNUMBER_FORGUID",-4020);
define("GUEST_ALLDEATILS_FORGUID_RES",-5010);
define("GUEST_ALLDEATILS_FORCONTACTNUMBER_RES",-5020);
define("GUEST_ALLDEATILS_FORNAME_RES",-5030);
define("GUEST_ALLSTATS_FORGUID_RES",-5040);

define("RR_ROOMID_FORROOMNAMES",11000);
define("RR_CHECK_ROOMID_INDB",11010);
define("RR_ROOMDETAILS_FOR_ROOMID",11020);
define("RR_CHECK_ROOMNAME_INDB",11030);
define("RR_GET_ALIVEDEADSTATUS_FORROOMNAMES",11040);
define("RRT_CHECK_TABLENAME_INDB",11050);
define("RRT_TABLEDETAILS_FOR_TBIDS",11060);
define("RR_ROOMDETAILS_FOR_GIVENSTATUS",11070);
define("RR_NUM_ACTIVE_TABLES_FOR_ROOMID",11080);
define("RR_ROOMID_FOR_GIVENSTATUS",11090);
define("RRT_TBID_FOR_TABLENAMES",11100);
define("ROOMTABLEDETAILS_FOR_ROOMIDARRAY",11110);
define("ACTIVETABLE_ROOM_DETAILS_FOR_ROOMIDARRAY",11120);
define("RRT_TABLENAMES_FOR_TBID",11130);
define("ACTIVETABLE_ROOM_DETAILS_TBID_INCLUDED_FOR_ROOMIDARRAY",11140);


define("DB_TRS1", "TRS1");
define("DBT_CURRENTBOOKINGS", "currentbookings");
define("DBT_CURRENTBOOKINGSTABLE", "cbtables");
define("DBT_RESTABLES", "restables");
define("DBT_WAITINGLIST", "waitinglist");
define("DBT_ADVANCEBOOKINGS","advancebookings");
define("DBT_ABTABLES","abtables");
define("DBT_GUESTS","guests");
define("DBT_RESROOMS","resrooms");
define("DBT_RESROOMSTABLES","resrooms_tables");


//*******Column names for Table currentbookings in database TRS1************
define("CB_HRID",DBT_CURRENTBOOKINGS.".HRID");//CURRENTBOOKING
define("CB_CBID",DBT_CURRENTBOOKINGS.".CBID");
define("CB_USERID",DBT_CURRENTBOOKINGS.".UserID");
define("CB_STATUS",DBT_CURRENTBOOKINGS.'.Status');
define("CB_STARTTIME",DBT_CURRENTBOOKINGS.".StartTime");
define("CB_ENDTIME",DBT_CURRENTBOOKINGS.".EndTime");
define("CB_EXPDURATION",DBT_CURRENTBOOKINGS.".ExpectedDuration");
define("CB_GUESTNUM",DBT_CURRENTBOOKINGS.".GuestNum");
define("CB_NOTES",DBT_CURRENTBOOKINGS.".Notes");
define("CB_GUESTUID",DBT_CURRENTBOOKINGS.".GuestUID");
//define("CB_GUESTNAMES",DBT_CURRENTBOOKINGS.".GuestNames");
//define("CB_GUESTCONTACTNOS",DBT_CURRENTBOOKINGS.".GuestContactNos");
//define("CB_EMAIL",DBT_CURRENTBOOKINGS.".GuestEmail");
//define("CB_COMMENT",DBT_CURRENTBOOKINGS.".Comment");
define("CB_WAITINGID",DBT_CURRENTBOOKINGS.".WaitingID");
define("CB_ADVANCEID",DBT_CURRENTBOOKINGS.".AdvanceID");
//***************************************************************************

//*******Column names for Table waitinglist in database TRS1************
define("WL_HRID",DBT_WAITINGLIST.".HRID");//CURRENTBOOKING
define("WL_WAITID",DBT_WAITINGLIST.".WaitID");
define("WL_USERID",DBT_WAITINGLIST.".UserID");
define("WL_STATUS",DBT_WAITINGLIST.".Status");
define("WL_STARTTIME",DBT_WAITINGLIST.".StartTime");
define("WL_ENDTIME",DBT_WAITINGLIST.".EndTime");
define("WL_EXPDURATION",DBT_WAITINGLIST.".ExpectedDuration");
define("WL_GUESTNUM",DBT_WAITINGLIST.".GuestNum");
define("WL_NOTES",DBT_WAITINGLIST.".Notes");
define("WL_GUESTUID",DBT_WAITINGLIST.".GuestUID");
//define("WL_GUESTNAMES",DBT_WAITINGLIST.".GuestNames");
//define("WL_GUESTCONTACTNOS",DBT_WAITINGLIST.".GuestContactNos");
//define("WL_EMAIL",DBT_WAITINGLIST.".GuestEmail");
//define("WL_COMMENT",DBT_WAITINGLIST.".Comment");
define("WL_ADVANCEID",DBT_WAITINGLIST.".AdvanceID");
//***************************************************************************

//*******Column names for Table cbtables in database TRS1************
define("CBT_HRID", DBT_CURRENTBOOKINGSTABLE.".HRID");
define("CBT_TBID", DBT_CURRENTBOOKINGSTABLE.".TBID");
define("CBT_CBID", DBT_CURRENTBOOKINGSTABLE.".CBID");
define("CBT_STARTTIME", DBT_CURRENTBOOKINGSTABLE.".StartTime");
define("CBT_ENDTIME", DBT_CURRENTBOOKINGSTABLE.".EndTime");

//*******Column names for Table restables in database TRS1************
define("RT_HRID", DBT_RESTABLES.".HRID");
define("RT_TBID", DBT_RESTABLES.".TBID");
define("RT_DISPLAYNAME", DBT_RESTABLES.".DisplayName");
define("RT_MINCAPACITY", DBT_RESTABLES.".MinCapacity");
define("RT_MAXCAPACITY", DBT_RESTABLES.".MaxCapacity");
define("RT_STATUS", DBT_RESTABLES.".Status");
define("RT_ONLINESTATUS", DBT_RESTABLES.".OnlineStatus");

//*******Column names for Table abtables in database TRS1************
define("ABT_HRID",DBT_ABTABLES.".HRID");
define("ABT_ABID",DBT_ABTABLES.".ABID");
define("ABT_TBID",DBT_ABTABLES.".TBID");
define("ABT_ALIVE",DBT_ABTABLES.".Alive");

//*******Column names for Table advancebookings in database TRS1************
define("AB_HRID",DBT_ADVANCEBOOKINGS.".HRID");
define("AB_ABID",DBT_ADVANCEBOOKINGS.".ABID");
define("AB_USERID",DBT_ADVANCEBOOKINGS.".UserID");
define("AB_BOOKINGMETHOD",DBT_ADVANCEBOOKINGS.".BookingMethod");
define("AB_STATUS",DBT_ADVANCEBOOKINGS.".Status");
define("AB_TABLESTATUS",DBT_ADVANCEBOOKINGS.".TableStatus");
define("AB_ONDATETIME",DBT_ADVANCEBOOKINGS.".OnDateTime");
define("AB_FORDATETIME",DBT_ADVANCEBOOKINGS.".ForDateTime");
define("AB_EXPECTEDDURATION",DBT_ADVANCEBOOKINGS.".ExpectedDuration");
define("AB_GUESTNUM",DBT_ADVANCEBOOKINGS.".GuestNum");
define("AB_GUESTUID",DBT_ADVANCEBOOKINGS.".GuestUID");
define("AB_NOTES",DBT_ADVANCEBOOKINGS.".Notes");
//define("AB_GUESTNAMES",DBT_ADVANCEBOOKINGS.".GuestNames");
//define("AB_GUESTCONTACTNOS",DBT_ADVANCEBOOKINGS.".GuestContactNos");
//define("AB_GUESTEMAIL",DBT_ADVANCEBOOKINGS.".GuestEmail");
//define("AB_GUESTCOMMENT",DBT_ADVANCEBOOKINGS.".GuestComment");

//******************Column Names for Table guests******************************
define("GUEST_HRID",DBT_GUESTS.".HRID");
define("GUEST_CONTACTNUMBER",DBT_GUESTS.".PrimaryContactNumber");
define("GUEST_ALTERNATECONTACTNUMBER",DBT_GUESTS.".AlternateContactNumber");
define("GUEST_NAME",DBT_GUESTS.".Name");
define("GUEST_EMAIL",DBT_GUESTS.".Email");
define("GUEST_COMMENT",DBT_GUESTS.".Comment");
define("GUEST_UID",DBT_GUESTS.".UID");

define("OUT_GUEST_HRID","GuestHRID");
define("OUT_GUEST_CONTACTNUMBER","GuestPrimaryContactNumber");
define("OUT_GUEST_ALTERNATECONTACTNUMBER","GuestAlternateContactNumber");
define("OUT_GUEST_NAME","GuestName");
define("OUT_GUEST_EMAIL","GuestEmail");
define("OUT_GUEST_COMMENT","GuestComment");
define("OUT_GUEST_UID","GuestUID");

define("OUT_GUID_WALKINCOUNT","WalkIn");

//***************************Column Names for ResRooms and ResRooms_Tables
define("RESROOMS_HRID",DBT_RESROOMS.".HRID");
define("RESROOMS_ROOMID",DBT_RESROOMS.".RoomID");
define("RESROOMS_ROOMNAME",DBT_RESROOMS.".RoomName");
define("RESROOMS_CREATEDTIME",DBT_RESROOMS.".CreatedTime");
define("RESROOMS_ISALIVE",DBT_RESROOMS.".IsAlive");

define("RRT_HRID",DBT_RESROOMSTABLES.".HRID");
define("RRT_ROOMID",DBT_RESROOMSTABLES.".RoomID");
define("RRT_TBID",DBT_RESROOMSTABLES.".TBID");
define("RRT_DISPLAYNAME",DBT_RESROOMSTABLES.".DisplayName");
define("RRT_MINCAPACITY",DBT_RESROOMSTABLES.".MinCapacity");
define("RRT_MAXCAPACITY",DBT_RESROOMSTABLES.".MaxCapacity");
define("RRT_STATUS",DBT_RESROOMSTABLES.".Status");
define("RRT_ONLINESTATUS",DBT_RESROOMSTABLES.".OnlineStatus");

define("OUT_RRT_HRID","HRID");
define("OUT_RRT_ROOMNAME","RoomName");
define("OUT_RRT_ROOMID","RoomID");
define("OUT_RRT_CREATEDTIME","CreateTime");
define("OUT_RRT_ISALIVE","RoomStatus");
define("OUT_RRT_TABLEID","TBID");
define("OUT_RRT_TABLENAME","TableName");
define("OUT_RRT_MINCAPACITY","MinCapacity");
define("OUT_RRT_MAXCAPACITY","MaxCapacity");
define("OUT_RRT_TABLESTATUS","TableStatus");
define("OUT_RRT_TABLESTATUS_ONLINEAVAILABILITY","OnlineAvailability");

define("OUT_RRT_NUM_ACTIVE_TABLES","ActiveTables");

//USER report error
define("USER_TABLEAVAILABILITY","Tables already booked, please choose different table.");
define("USER_EMPTYTABLELIST","Please select atleast one table for booking");
define("USER_INVALIDTIMEDATE", "Invalid date or time, please try again");
define("USER_DBACTIONFAILED", "Action Failed, please try again");
define("USER_INVALIDTIMEDURATION", "Invalid Expected Duration, action failed");
define("USER_INVALIDGUESTNUM", "Guest Numbers empty or not numeric, please try again");
define("USER_INVALIDLIMITS","Please enter correct start and end limits");
define("USER_INVALIDTYPE_GETBOOKINGS_FORGUID","Please choose correct Type for the bookings you want to see.");
define("USER_MAXLIMIT_NOMORERECORDS","Too many records to retrieve, please contact our team for further help for old data.");

//***********current booking user view exceptions*********************
define("USER_CBINVALIDSTATUS", "Invalid Status for current bookings, please try again");
define("USER_CBCLOSEFAIL", "Unable to close the booking, please try again");
define("USER_CBCLOSEFAILALREADYCLOSED", "The selected booking is already closed, please select a different entry");
define("USER_CBADDTABLEFAIL", "Unable to add tables to the current booking, please try again or select different table");
define("USER_CB_REMOVETABLE_FAIL", "Unable to close the tables fromt the current booking, please try again or select different table");
define("USER_CBADDTABLEFAILBOOKINGCLOSED", "Can not add tables to a closed booking, please try again or select different booking");
define("USER_CB_UPDATE_EXPTECTEDDURATION_FAIL_BOOKINGCLOSED", "Can not update the duration of a booking which has been closed, please try again or select different booking");
define("USER_CBUPDATE_FAIL_BOOKINGCLOSED", "Unable to update a closed booking, please select a different booking");
define("USER_CBUPDATE_FAIL_DBTRANSACT_ERROR", "Unable to update this booking now, please try again");
define("USER_INVALIDTABLEID", "Supplied Table Names not available in the list, action failed");
define("USER_CB_INVALIDCBID", "Invalid booking id, action failed");

//***********waiting list user view exceptions*********************
define("USER_WLINVALIDSTATUS", "Invalid Status for waiting list, please try again");
define("USER_WLINVALIDID", "Invalid waiting list ID, please try again");
define("USER_WLCLOSEFAIL", "Unable to close the waiting list entry, please try again");
define("USER_WLCLOSEFAILALREADYCLOSED", "The selected waiting list entry is already closed, please select a different entry");
define("USER_WLUPDATE_FAIL_CLOSED", "Unable to update a closed waiting list entry, please select a different entry");
//***********advance booking user view exceptions*********************
define("USER_ABSTATUSUPDATEFAIL", "Status update failed for advance booking, please try again");
define("USER_ABREMOVETABLEFAIL", "Unable to remove table for advance booking, please try again");
define("USER_ABFORDATEWRONG", "Advance booking date invalid or pre-dated, please enter a future date & time ");
define("USER_ABASSIGNTABLEFAILFORDATEPAST", "Assign table failed, can not assign table for a booking that is in past.");
define("USER_ABASSIGNTABLEINVALIDSTATUS", "Assign table failed, can not assign table for a booking that whoose status stands cancelled or no-show.");
define("USER_ABINVALIDSTATUS", "Invalid Status for advance booking, please try again");
define("USER_ABINVALIDTABLESTATUS", "Invalid Status for Table in advance booking,please try again");
define("USER_ABINVALIDBOOKINGMETHOD", "Invalid booking method for advance booking, please try again");

//*****************************************ResRoomsTables***************
define("USER_RRT_INVALIDROOMNAME","Please enter only alphabets and numbers in the room name.");
define("USER_RRT_ROOMNAME_ALREADYEXISTS","The supplied room name already exists, please choose another name");
define("USER_RRT_ROOMCREATE_FAILED","Oops, room creation failed, please try again.");
define("USER_RRT_ROOMDETAILS_FAILED","The room id supplied doesnt exist.");
define("USER_RRT_ROOMNAME_NOTFOUND","Please enter a valid room name");
define("USER_RRT_ROOMNAMEUPDATE_FAILED","Room name update failed, please try again.");
define("USER_RRT_ACTIVATE_DEACTIVATE_FAILED","Room Activation/Decativation failed, status unrecognisable.");
define("USER_RRT_ALIVEDEAD_STATUSINVALID","Action failed, status invalid.");
define("USER_RRT_STATUSINVALID","Action failed, status invalid.");
define("USER_RRT_NOROOMS_FORSTATUS","No rooms for given status.");

define("USER_RRT_INVALIDTABLENAME","Please enter only alphabets and numbers in the table name.");
define("USER_RRT_TABLENAME_ALREADYEXISTS","The supplied table name already exists in the given room, please choose another name");
define("USER_RRT_INVALIDCAPACITY","Please enter correct min and max capacity");
define("USER_RRT_INVALID_ONLINESTATUS","Please enter correct online status");
define("USER_RRT_INVALID_ONLINESTATUSCODE","Can not get online status");
define("USER_RRT_INVALID_TABLE_STATUSCODE","Unable to detemine table status");
define("USER_RRT_TABLEDETAILS_FAILED","The supplied table-id doesnt exist.");
define("USER_RRT_TABLE_NAMEUPDATE_FAILED","Table name update failed, please try again.");
//*******************GUEST User View Exceptions
define("USER_GUEST_INVALIDCONTACT", "Invalid Contact number, please include only digits or leave blank.");
define("USER_GUEST_INVALIDUID", "Invalid guest id, action failed");
define("USER_GUEST_INFOUPDATEFAIL", "Guest details not updated.");
define("USER_GUEST_INSERTFAILED", "Unable to insert the guest details, please try again or leave them blank");
define("USER_GUEST_CONTACT_NOTIN_DB", "Contact number doesn't exist in the records");
define("USER_GUEST_CONTACT_EMPTY", "Passed Contact number is blank, please pass a valid contact");
//*******Exceptions************
define("EXCP_ADDTABLE", "Exception_AddTable::Unable to add table, action failed");
define("EXCP_TABLEAVAILABILITY", "Exception_TableAvailable::Table already booked, action failed");
define("EXCP_CBINVALIDSTATUS", "Exception_CurrentBookingInvalidStatus::Invalid Status for current bookings!!");
define("EXCP_CB_TRANSACTIONSTART_FAIL", "Exception_CurrentBookingTransactionStartFailed::Fatal Error, transaction startr failed, Error logged as an SQL error too");

define("EXCP_INVALIDTABLEID", "Exception_InvalidTableID::Supplied TBID(s) not available in the list, action failed");
define("EXCP_WLINVALIDSTATUS", "Exception_WaitingListInvalidStatus::Invalid Status for waiting list, action failed");
define("EXCP_WLINVALIDID", "Exception_WaitingListInvalidID::Invalid waiting list ID, action failed");

define("EXCP_ABINVALIDSTATUS", "Exception_AdvanceBookingInvalidStatus::Invalid Status for advance booking, action failed");
define("EXCP_ABINVALIDBOOKINGMETHOD", "Exception_AdvanceBookingInvalidBookingMethod::Invalid booking method for advance booking, action failed");

define("EXCP_GUEST_INVALIDSTATUS", "Exception_GuestInvalidStatus::Invalid Status for guests, action failed");

define("EXCP_DBERR111", "Exception_DBERR111::Unable to connect to database , please try again later");
define("EXCP_DBERR123", "Exception_DBERR123::Unable to book table, reservation failed");
define("EXCP_DBERR133", "Exception_DBerr133::Unable to book reservation, reservation failed");
define("EXCP_DBERR153", "Exception_DBERR153::Unable to get current table list, reservation failed");
define("EXCP_DBERR253", "Exception_DBERR253::Unable to get restaurant table list, reservation failed");
define("EXCP_DBERR343", "Exception_DBERR343::Table currently occupied, reservation failed");
define("EXCP_DBERR333", "Exception_DBERR333::Supplied TBID(s) not available, action failed");
define("EXCP_DBERR769", "Exception_DBERR769::Close Action Failed, please try again");
define("EXCP_DBERR729", "Exception_DBERR729::Booking to close doesn't exist or already closed!!");

define("EXCP_DBERR1073", "Exception_DBERR1073::Error in update string , action cancelled");
define("EXCP_DBERR1083", "Exception_DBERR1083::Error in update_set string , action cancelled");

define("EXCP_CBERR188", "Exception_CBERR188::Invalid booking id, action failed");
define("EXCP_CBERR178", "Exception_CBERR178::Invalid WaitID, action failed");
define("EXCP_CBERR168", "Exception_CBERR168::Wrong UserID, action failed");
define("EXCP_CBERR158", "Exception_CBERR158::Guest Numbers empty or not numeric, action failed");
define("EXCP_CBERR2158", "Exception_CBERR2158::Invalid Status for current bookings!!");
define("EXCP_CBERR1883", "Exception_CBERR1883::Guest Info update failed!!");

define("EXCP_ERR143", "Exception_ERR143::Table ID empty, reservation failed");
define("EXCP_ERR198", "Exception_ERR198::Invalid AdvanceID, action failed");
define("EXCP_ERR598", "Exception_ERR598::Invalid Default Duration, action failed");
define("EXCP_ERR588", "Exception_ERR588::Invalid Request, action failed");
define("EXCP_ERR988", "Exception_ERR988::Action failed, please try again");
define("EXCP_ERR2588", "Exception_ERR2588::Invalid date or time, please try again");
define("EXCP_ERR2558", "Exception_ERR2558::Advance booking date invalid or pre-dated");

define("EXCP_WLERR178", "Exception_WLERR178::Invalid WaitID, action failed");
define("EXCP_WLERR198", "Exception_WLERR198::Invalid AdvanceID, action failed");
define("EXCP_DBWLERR133", "Exception_DBWLerr133::Unable add to waiting list, action failed");
define("EXCP_WLERR729", "Exception_WLERR729::Entry to close doesn't exist or already closed!!");
define("EXCP_WLERR739", "Exception_WLERR739::Invalid Status for the waiting list entry!!");

define("EXCP_DBABEERR133", "Exception_DBABEERR133::Unable to update advance booking, action failed");
define("EXCP_ABEERR111", "Exception_ABEERR111::Invalid type for booking method!!");
define("EXCP_ABEERR121", "Exception_ABEERR121::Invalid type for Status!!");
define("EXCP_ABEERR131", "Exception_ABEERR131::Invalid type for Table Status!!");
define("EXCP_ABEERR311", "Exception_ABEERR311::Table Status and Table set-u action mismatch!!");
define("EXCP_ABERR588",  "Exception_ABERR588::Invalid Status, action failed");
define("EXCP_ABERR188",  "Exception_ABERR188::Invalid booking id, action failed");
define("EXCP_ABERR888",  "Exception_ABERR888::Empty fields passed, action failed");

define("EXCP_RRT_INVALIDSTATUS", "Exception_ResRoomsInvalidStatus::Invalid Status!!");

//**************************************DBOperations*********************
define("EXCP_DBO_DATABASECONNECT", "Exception_DBO_DATABASECONNECT::Unable to connect the database!!");
define("EXCP_DBO_TRANSACTIONSTART_FAILED", "Exception_DBO_TransactionStartFailed::Unable to start transaction mode!!");
define("EXCP_DBO_DATABASESELECT", "Exception_DBO_DATABASESELECT::Unable to select the database!!");
define("EXCP_DBO_PREPARE", "Exception_DBO_PREPARE::Mysqli Prepare failed!!");
define("EXCP_DBO_SQLQUERY", "Exception_DBO_SQLQUERY::Mysqli Query failed!!");
define("EXCP_DBO_INSERTNUMCOLVAL", "Exception_DBO_INSERTNUMCOLVAL::Number of column names and values to insert dont match");
define("EXCP_DBO_INSERTUPDATENUMCOLVAL", "Exception_DBO_INSERTUPDATENUMCOLVAL::Number of values in insert and on duplicate to update dont match");
define("EXCP_DBO_UPDATENUMCOLVAL", "Exception_DBO_UPDATENUMCOLVAL::Number of values to update and number of columns to update dont match");
define("EXCP_DBO_EQUALORNUMCOLVAL", "Exception_DBO_EQUALORNUMCOLVAL::Number of column names and values dont match");

define("EXCP_DBO_BINDEXECUTE", "Exception_DBO_BINDEXECUTE::Bind parameters or execute failed");
?> 