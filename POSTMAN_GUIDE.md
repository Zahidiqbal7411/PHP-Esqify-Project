# How to Import and Use Postman Collection

## Quick Start Guide

### Step 1: Import Collection into Postman

1. **Open Postman** application
2. Click **Import** button (top left)
3. Click **Upload Files**
4. Select `Esqify_API_Postman_Collection.json` from your project folder
5. Click **Import**

✅ All 13+ API endpoints are now ready to test!

---

### Step 2: Test Your First API

**Example: Test "Get Bars List"**

1. In Postman, expand the collection: **Esqify Mobile API - Complete Collection**
2. Click on **NEW APIS** folder
3. Click **1. Get Bars List**
4. Click blue **Send** button
5. View response in the panel below

---

## API Testing Quick Reference

### 📋 NEW APIs Created (5 endpoints)

| # | API Name | Method | Quick Test |
|---|----------|--------|------------|
| 1 | Get Bars List | GET | Just click Send ✓ |
| 2 | Get Cities List | POST | Pre-filled JSON, click Send ✓ |
| 2b | Get All Cities | POST | No filter version |
| 3 | Get Positions List | GET | Just click Send ✓ |
| 4 | Get Chat List | POST | Change user_id to test |
| 5 | Get Messages | POST | Change chat_id & user_id |

### 🔧 FIXED APIs (3 endpoints)

| # | API Name | Method | What Was Fixed |
|---|----------|--------|----------------|
| 6 | FAQs List | GET | Error 500 → Now works ✓ |
| 7 | Deal Details | POST | Photos now full URLs ✓ |
| 8 | Leaderboard | POST | Error 500 → Now works ✓ |
| 8b | Leaderboard w/ Filters | POST | With state/industry filters |

### ✏️ UPDATED APIs (2 endpoints)

| # | API Name | Method | What Changed |
|---|----------|--------|--------------|
| 9 | Job Create Form | GET | Now includes positions ✓ |
| 10 | Deal Create Form | GET | Now includes bars ✓ |

### ✔️ EXISTING APIs (4 endpoints)

| # | API Name | Method | Notes |
|---|----------|--------|-------|
| 11 | Send Message | POST | Text messages |
| 11b | Send Message w/ Image | POST | With file upload |
| 12 | Post Deal | POST | Create deal (has photo upload) |
| 13 | Post Job | POST | Create job posting |

---

## How to Modify Parameters

### For GET Requests (Simple)
Just click **Send** - no changes needed!

### For POST Requests with JSON Body

1. Click on the API endpoint
2. Go to **Body** tab
3. You'll see pre-filled JSON like:
   ```json
   {
       "user_id": 15,
       "page": 1
   }
   ```
4. **Change the values** as needed
5. Click **Send**

### For POST Requests with Form Data

1. Click on the API endpoint  
2. Go to **Body** tab → **form-data**
3. See list of key-value pairs
4. **Edit the values** in the VALUE column
5. Click **Send**

---

## Common Test Scenarios

### Scenario 1: Get Cities for a Specific State

1. Open **2. Get Cities List**
2. In Body, change:
   ```json
   {
       "state_id": 5,      ← Change this to your state ID
       "page": 1,
       "per_page": 50
   }
   ```
3. Click **Send**

### Scenario 2: Check User's Chats

1. Open **4. Get Chat List**
2. In Body, change:
   ```json
   {
       "user_id": 15      ← Change to actual user ID
   }
   ```
3. Click **Send**
4. Copy a `chat_id` from response

### Scenario 3: View Messages in a Chat

1. Open **5. Get Messages**
2. In Body, paste the chat_id:
   ```json
   {
       "chat_id": 42,     ← Use chat_id from previous step
       "user_id": 15,     ← Same user ID
       "page": 1,
       "per_page": 50
   }
   ```
3. Click **Send**

### Scenario 4: Test Leaderboard with Filters

1. Open **8b. Leaderboard with Filters**
2. Modify filters as needed:
   ```json
   {
       "state_for_search": 5,
       "industries_for_search": [1, 3, 5],
       "sort_data": "deal_volume",
       "page": 1,
       "per_page": 20
   }
   ```
3. Sort options: `deal_volume`, `deal_total`, `latest`, `oldest`, `ascending`, `descending`
4. Click **Send**

---

## Understanding Responses

### ✅ Success Response
```json
{
    "status": true,
    "message": "Data fetched successfully",
    "data": [...]
}
```
**Status Code**: 200 OK (green)

### ❌ Error Response
```json
{
    "status": false,
    "message": "Error description here"
}
```
**Status Code**: 400, 401, 405, or 500 (red/orange)

### 📊 Paginated Response
```json
{
    "status": true,
    "message": "Success",
    "page": 1,
    "per_page": 50,
    "total": 500,
    "total_pages": 10,
    "data": [...]
}
```

---

## Testing Workflow

### For App Developer (Testing Forms)

**Test Job Posting Flow:**
1. ✅ Get form data: **9. Job Create Form Data**
2. ✅ Get positions: **3. Get Positions List**  
3. ✅ Get cities: **2. Get Cities List** (with state filter)
4. ✅ Post job: **13. Post Job**

**Test Deal Posting Flow:**
1. ✅ Get form data: **10. Deal Create Form Data**
2. ✅ Get bars: **1. Get Bars List**
3. ✅ Get cities: **2. Get Cities List**
4. ✅ Post deal: **12. Post Deal**
5. ✅ View details: **7. Deal Details** (verify photo URLs)

**Test Chat Flow:**
1. ✅ Get chat list: **4. Get Chat List**
2. ✅ Get messages: **5. Get Messages**
3. ✅ Send message: **11. Send Message**

---

## Tips & Tricks

### 💡 Tip 1: Save Common Values
Create environment variables in Postman:
- Variable: `user_id` → Value: `15`
- Variable: `state_id` → Value: `5`

Then use: `{{user_id}}` in your requests

### 💡 Tip 2: Organize Tests
Create folders for:
- ✅ Working APIs
- 🔧 Testing/Debug
- ❌ Failed Tests

### 💡 Tip 3: Quick Duplicate
Right-click any request → **Duplicate** → Modify params → Test different scenarios

### 💡 Tip 4: View Full Response
Click **Pretty** tab for formatted JSON  
Click **Raw** tab to see exact server response

### 💡 Tip 5: Check Headers
Go to **Headers** tab (bottom) to see:
- Content-Type
- Response Time
- Size

---

## Troubleshooting

### Issue: "Could not get any response"
- ✅ Check internet connection
- ✅ Verify server is running: https://dev.esqify.com
- ✅ Check URL is correct

### Issue: "404 Not Found"
- ✅ Check file exists on server
- ✅ Verify path: `/mobile_api/filename.php`

### Issue: "500 Internal Server Error"
- ✅ Check server error logs
- ✅ Verify database is running
- ✅ Test with simpler parameters first

### Issue: "Missing required parameters"
- ✅ Check Body tab has correct data
- ✅ Verify Content-Type header
- ✅ For POST: use JSON or form-data as specified

---

## API Endpoint URLs (Copy-Paste Ready)

### New APIs
```
https://dev.esqify.com/mobile_api/get_bars_list.php
https://dev.esqify.com/mobile_api/get_cities_list.php
https://dev.esqify.com/mobile_api/get_positions_list.php
https://dev.esqify.com/mobile_api/get_chat_list.php
https://dev.esqify.com/mobile_api/get_messages.php
```

### Fixed APIs
```
https://dev.esqify.com/mobile_api/faqs_list.php
https://dev.esqify.com/mobile_api/details_deals.php
https://dev.esqify.com/mobile_api/leaderboard.php
```

### Updated APIs
```
https://dev.esqify.com/mobile_api/job_create.php?user_id=15
https://dev.esqify.com/mobile_api/deal_create.php?user_id=15
```

### Existing APIs
```
https://dev.esqify.com/mobile_api/new_chat.php
https://dev.esqify.com/mobile_api/post_deals.php
https://dev.esqify.com/mobile_api/post_job.php
```

---

## Next Steps

1. ✅ Import Postman collection
2. ✅ Test each NEW API (1-5)
3. ✅ Verify FIXED APIs work (6-8)
4. ✅ Check UPDATED APIs include new data (9-10)
5. ✅ Test full workflows (job posting, deal posting, chat)
6. ✅ Report any issues found

---

**Need Help?**
- 📖 See full documentation: `API_DOCUMENTATION.md`
- 🐛 Found a bug? Check server logs
- ✉️ Contact: API Dev team
