### Seat API Endpoints

#### 1. Get Seats Status
- **Name**: Get Seats Status
- **Endpoint**: `GET /seats`
- **Description**: 根據提供的時間範圍，返回所有座位的狀態。
- **Input**: 
  - Query Params: 
    - `timeFilter`: (optional) `{ "beginTime": "ISO8601 格式的日期時間", "endTime": "ISO8601 格式的日期時間" }`
      - 若提供 `beginTime` 或 `endTime`，則兩者都必須提供。
- **Output**:
  - 格式: `[ { "seatId": "string", "seatCode": "string", "status": "string" } ]`
    - `seatId`: 座位的 ID
    - `seatCode`: 座位代碼
    - `status`: 座位狀態，可能的值為：
      - `available`: 可用
      - `unavailable`: 不可用
      - `reserved`: 已預約
      - `partiallyReserved`: 部分預約
- **Status Codes**:
  - 200: 請求成功
  - 400: 輸入參數錯誤
  - 422: 時間範圍外的查詢

---

#### 2. Get Seat Status
- **Name**: Get Seat Status
- **Endpoint**: `GET /seats/{seatId}`
- **Description**: 獲取指定座位的所有預約狀態。
- **Input**: 
  - 路徑參數: 
    - `seatId`: 必填，座位的 ID
- **Output**:
  - 格式: `[ { "beginTime": "ISO8601 格式的日期時間", "endTime": "ISO8601 格式的日期時間" } ]`
    - 每個對象描述座位在指定時間內的預約情況。
- **Status Codes**:
  - 200: 請求成功
  - 404: 座位不存在

---

#### 3. Update Seat
- **Name**: Update Seat
- **Endpoint**: `PUT /seats/{seatId}`
- **Description**: 更新指定座位的可用狀態。
- **Input**:
  - 路徑參數: 
    - `seatId`: 必填，座位的 ID
  - Body: 
    ```json
    {
      "available": "boolean"
    }
    ```
    - `available`: 必填，座位是否可用的狀態
- **Output**: 無
- **Status Codes**:
  - 204: 更新成功
  - 400: 更新失敗
  - 404: 座位不存在