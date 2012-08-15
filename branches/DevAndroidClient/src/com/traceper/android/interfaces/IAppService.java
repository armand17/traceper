package com.traceper.android.interfaces;

import org.json.JSONArray;
import org.json.JSONObject;



public interface IAppService {
	
	public static final int HTTP_REQUEST_FAILED = 0;	
	//protocol defined statics
	public static final int HTTP_RESPONSE_SUCCESS = 1;
	public static final int HTTP_RESPONSE_ERROR_UNKNOWN = -1;
	public static final int HTTP_RESPONSE_ERROR_MISSING_PARAMETER = -2;
	public static final int HTTP_RESPONSE_ERROR_UNSUPPORTED_ACTION = -3;
	public static final int HTTP_RESPONSE_ERROR_UNAUTHORIZED_ACCESS = -4;
	public static final int HTTP_RESPONSE_ERROR_EMAIL_EXISTS = -5;
	public static final int HTTP_RESPONSE_ERROR_EMAIL_NOT_VALID = -9;
	//self-defined error	
	public static final int HTTP_RESPONSE_ERROR_UNKNOWN_RESPONSE = -100;

		
//	public static final int ACTION_LAST_LOCATION_DATA_SENT_TIME = 1001;
	public static final String LAST_LOCATION_DATA_SENT_TIME = "LAST_LOCATION_DATA_SENT_TIME";
	public static final String LAST_LOCATION = "LOCATION"; 
	
	
	public static final String SHOW_MY_LOCATION = "SHOW_MY_LOCATION";
	public static final String SHOW_USER_LOCATION = "SHOW_USER_LOCATION";
	public static final String SHOW_ALL_USER_LOCATIONS = "SHOW_ALL_USER_LOCATIONS";
	
	public static final String SHOW_USER_PAST_POINT = "SHOW_USER_PAST_POINT";
	public static final String SHOW_USER_PAST_POINT_ON_MAP = "SHOW_USER_PAST_POINT_ON_MAP";
	public static final String SHOW_USER_ALL_PAST_POINT_ON_MAP = "SHOW_USER_ALL_PAST_POINT_ON_MAP";
	
	public static final JSONObject SHOW_USER_LOCATION_LOC = null;
	
	public static final String SHOW_USER_LOCATION_LATITUDE = "SHOW_USER_LOCATION_LATITUDE";
	public static final String SHOW_USER_LOCATION_LONGITUDE = "SHOW_USER_LOCATION_LONGITUDE";
	
	public static final String SHOW_USER_SEARCH_LIST = "SHOW_USER_SEARCH_LIST";
	public static final String SHOW_USER_INVITATION_LIST="SHOW_USER_INVITATION_LIST";
	
	public String getUsername();
	
	public boolean isNetworkConnected();
	
	public boolean isUserAuthenticated();
	
	public void setAutoCheckin(boolean enable);
	
	public void sendLocationNow();
	
	public Long getLastLocationSentTime();
	
	public void exit();
	
	public JSONObject getUserInfo(int userid);
	
	public String registerUser(String password, String email, String realname, String facebookId);
	
	public JSONArray SearchJSON(String search);
	
	public String registerFBUser(String password, String email, String realname, String fb_id);
	
	public String registerGPUser(String password, String email, String realname, String image, String gp_id);
	
	public String authenticateUser(String username, String password, String facebookId);
	
	public String AddAsFriend(String FriendId);
	
	public String approveFriendShip(String friendShipId);
	
	public JSONArray GetFriendRequestListJson(); 
	
	public void setAuthenticationServerAddress(String address);
	
	public boolean uploadImage(byte[] image, boolean publicData, String description);

	public boolean uploadVideo(byte[] video, boolean publicData, String description);
	
	public JSONArray getUserList();
	
	public JSONArray getUserPlaces(int userid);


	
}
