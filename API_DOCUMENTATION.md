# Telegram Clone API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "date_of_birth": "1990-01-01"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123",
    "device_type": "web",
    "device_name": "Chrome Browser"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

### Users

#### Get User Profile
```http
GET /api/users/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/users/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "username": "newusername",
    "full_name": "New Full Name",
    "bio": "Updated bio"
}
```

#### Upload Profile Image
```http
POST /api/users/profile/image
Authorization: Bearer {token}
Content-Type: multipart/form-data

image: [file]
is_primary: true
```

#### Search Users
```http
GET /api/users/search?query=john&limit=20
Authorization: Bearer {token}
```

### Chats

#### Get User's Chats
```http
GET /api/chats
Authorization: Bearer {token}
```

#### Create Chat
```http
POST /api/chats
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "private",
    "user_ids": [2]
}
```

#### Get Specific Chat
```http
GET /api/chats/{id}
Authorization: Bearer {token}
```

#### Update Chat
```http
PUT /api/chats/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Group Name",
    "description": "Updated description"
}
```

#### Add Member to Chat
```http
POST /api/chats/{id}/members
Authorization: Bearer {token}
Content-Type: application/json

{
    "user_id": 3,
    "role": "member"
}
```

#### Remove Member from Chat
```http
DELETE /api/chats/{id}/members/{userId}
Authorization: Bearer {token}
```

### Messages

#### Get Chat Messages
```http
GET /api/messages/chat/{chatId}?per_page=20
Authorization: Bearer {token}
```

#### Send Message
```http
POST /api/messages
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": 1,
    "message_type": "text",
    "content": "Hello, world!"
}
```

#### Send Media Message
```http
POST /api/messages
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": 1,
    "message_type": "image",
    "file_url": "https://example.com/image.jpg",
    "file_name": "photo.jpg",
    "file_size": 1024000
}
```

#### Reply to Message
```http
POST /api/messages
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": 1,
    "message_type": "text",
    "content": "This is a reply",
    "reply_to_message_id": 5
}
```

#### Mark Message as Read
```http
POST /api/messages/{id}/read
Authorization: Bearer {token}
```

#### Start Typing
```http
POST /api/messages/typing
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": 1
}
```

#### Stop Typing
```http
DELETE /api/messages/typing
Authorization: Bearer {token}
Content-Type: application/json

{
    "chat_id": 1
}
```

## Response Format

All API responses follow this format:

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Chat Types

- `private` - One-on-one chat
- `group` - Group chat with multiple members
- `channel` - Broadcast channel

## Message Types

- `text` - Text message
- `image` - Image file
- `video` - Video file
- `audio` - Audio file
- `file` - General file
- `voice` - Voice message
- `location` - Location data

## User Roles

- `owner` - Chat owner (can manage everything)
- `admin` - Chat admin (can manage members)
- `member` - Regular member
