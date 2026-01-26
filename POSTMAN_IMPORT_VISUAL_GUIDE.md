# 📥 Postman Collection Import Guide - Step by Step

## Visual Guide with Numbered Steps

---

## Method 1: Using Import Button (Recommended)

### Step 1️⃣: Click the Import Button
```
Location: Top-left area of Postman, in the main toolbar
Look for: "Import" button (usually orange/coral color)
Action: Click on "Import"
```

**Visual Indicator:**
```
┌─────────────────────────────────────────────┐
│  🏠 Home    Workspaces    API Network       │
│                                             │
│  [New ▼]  [📥 Import]  ← CLICK HERE        │
│                                             │
└─────────────────────────────────────────────┘
```

---

### Step 2️⃣: Choose Upload Method
```
Dialog Title: "Import"
Options Available:
  - File
  - Folder  
  - Link
  - Raw text
  - Code repository

Action: Click "Upload Files" button OR drag & drop
```

**Visual Indicator:**
```
┌───────────────────────────────────────┐
│  Import                          ✕    │
├───────────────────────────────────────┤
│                                       │
│  ┌─────────────────────────────────┐ │
│  │                                 │ │
│  │   📁  [Upload Files]            │ │ ← CLICK HERE
│  │                                 │ │
│  │   OR                            │ │
│  │                                 │ │
│  │   Drag & Drop Files Here        │ │
│  │                                 │ │
│  └─────────────────────────────────┘ │
│                                       │
│  [  Link  ]  [Raw text] [Repository] │
│                                       │
└───────────────────────────────────────┘
```

---

### Step 3️⃣: Select the JSON File
```
File Browser Opens
Navigate to: c:\xampp\htdocs\company_projects\laravel_projects\mobile_api_esqify\
Look for: Esqify_API_Postman_Collection.json
Action: Click on file, then click "Open"
```

**File to Select:**
```
📂 mobile_api_esqify/
  ├── 📄 API_DOCUMENTATION.md
  ├── 📄 POSTMAN_GUIDE.md
  ├── 📄 Esqify_API_Postman_Collection.json  ← SELECT THIS FILE
  ├── 📄 get_bars_list.php
  ├── 📄 get_cities_list.php
  └── ...
```

---

### Step 4️⃣: Confirm Import
```
Preview Screen Shows:
  - Collection name: "Esqify Mobile API - Complete Collection"
  - Number of requests: 13+
  - Folders structure preview

Action: Click "Import" button at the bottom
```

**Visual Indicator:**
```
┌───────────────────────────────────────────┐
│  Import Selected Items                    │
├───────────────────────────────────────────┤
│                                           │
│  ✅ Esqify Mobile API - Complete Collection│
│     └─ NEW APIS (5 requests)             │
│     └─ FIXED APIS (4 requests)           │
│     └─ UPDATED APIS (2 requests)         │
│     └─ EXISTING APIS (4 requests)        │
│                                           │
│           [Cancel]     [Import] ←CLICK    │
│                                           │
└───────────────────────────────────────────┘
```

---

### Step 5️⃣: Verify Import Success
```
Location: Left sidebar under "Collections"
Look for: "Esqify Mobile API - Complete Collection"
Action: Expand to see all folders and requests
```

**Success View:**
```
Collections
└── 📁 Esqify Mobile API - Complete Collection
    ├── 📁 NEW APIS
    │   ├── 1. Get Bars List (GET)
    │   ├── 2. Get Cities List (POST)
    │   ├── 2b. Get All Cities (POST)
    │   ├── 3. Get Positions List (GET)
    │   ├── 4. Get Chat List (POST)
    │   └── 5. Get Messages (POST)
    │
    ├── 📁 FIXED APIS
    │   ├── 6. FAQs List (FIXED) (GET)
    │   ├── 7. Deal Details (FIXED) (POST)
    │   ├── 8. Leaderboard (FIXED) (POST)
    │   └── 8b. Leaderboard with Filters (POST)
    │
    ├── 📁 UPDATED APIS
    │   ├── 9. Job Create Form Data (UPDATED) (GET)
    │   └── 10. Deal Create Form Data (UPDATED) (GET)
    │
    └── 📁 EXISTING APIS (Unchanged)
        ├── 11. Send Message (Chat) (POST)
        ├── 11b. Send Message with Image (POST)
        ├── 12. Post Deal (POST)
        └── 13. Post Job (POST)
```

---

## Method 2: Drag & Drop (Quick Method)

### Visual Flow:
```
Step 1: Open File Explorer
   ├─→ Navigate to: C:\xampp\htdocs\...\mobile_api_esqify\
   └─→ Locate: Esqify_API_Postman_Collection.json

Step 2: Click and hold the JSON file
   └─→ Drag it to Postman window

Step 3: Drop anywhere in Postman
   └─→ Import dialog automatically opens

Step 4: Confirm Import
   └─→ Click "Import" button
```

**Screen Layout:**
```
┌──────────────────┬────────────────────────┐
│  File Explorer   │   Postman Window       │
│                  │                        │
│  📄 Esqify...json│   Drop file here →    │
│      ↓           │         ⤵             │
│      └──────────────────→  💾            │
│                  │                        │
└──────────────────┴────────────────────────┘
```

---

## Common Issues & Solutions

### ❌ Issue 1: "Import" button not visible
**Solution:**
- Make sure Postman is fully loaded
- Try pressing `Ctrl + O` (keyboard shortcut for Import)
- Update Postman to latest version

### ❌ Issue 2: File not showing in browser
**Solution:**
- Change file filter to "All Files (*.*)"
- Navigate using full path in address bar
- Copy full path: `c:\xampp\htdocs\company_projects\laravel_projects\mobile_api_esqify\Esqify_API_Postman_Collection.json`

### ❌ Issue 3: Import fails or shows error
**Solution:**
- Verify JSON file is not corrupted (open in text editor)
- File size should be around 15-20 KB
- Close and reopen Postman
- Try Method 2 (Drag & Drop)

### ❌ Issue 4: Collection imported but empty
**Solution:**
- Delete the collection
- Re-import using Method 1
- Check if file was modified accidentally

---

## After Import - Quick Test

### Testing Your First API:

1. **Expand** the collection in left sidebar
2. **Click** on folder "NEW APIS"
3. **Select** "1. Get Bars List"
4. **Check** the URL is correct
5. **Click** blue "Send" button

### Expected Result:
```json
{
  "status": true,
  "message": "Bars fetched successfully.",
  "count": 50,
  "data": [...]
}
```

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Open Import Dialog | `Ctrl + O` |
| New Request | `Ctrl + N` |
| Save Current Request | `Ctrl + S` |
| Search Collection | `Ctrl + F` |
| Send Request | `Ctrl + Enter` |

---

## Import Success Checklist

- [ ] Import button clicked
- [ ] JSON file selected and uploaded
- [ ] Preview shows "Esqify Mobile API - Complete Collection"
- [ ] Import confirmed
- [ ] Collection visible in left sidebar
- [ ] Collection contains 4 folders
- [ ] All 13+ requests are visible
- [ ] First request (Get Bars List) opens successfully

---

## Video Tutorial (Alternative)

If you prefer a video tutorial, you can:
1. Record your screen while following these steps
2. Or search YouTube for: "How to import Postman collection JSON"

---

## Need Help?

- 📖 See full API documentation: `API_DOCUMENTATION.md`
- 📘 Postman usage guide: `POSTMAN_GUIDE.md`
- 🐛 If import fails, try drag & drop method
- 💬 Contact API developer for support

---

## Next Steps After Import

1. ✅ Update all URLs from `dev.esqify.com` to `localhost`
2. ✅ Test each API in order (NEW → FIXED → UPDATED)
3. ✅ Verify responses match documentation
4. ✅ Report any issues found

---

**🎉 Congratulations!** You now have all APIs ready to test in Postman!
