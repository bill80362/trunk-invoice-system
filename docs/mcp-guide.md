# 貨運請款單系統 MCP 連線設定指南

## 步驟一：取得 API Token

在系統後台 **設定 → MCP Token** 新增一組 Token，依需求選擇權限：

| 使用情境 | 建議權限 |
|---|---|
| 只查詢 | clients:read、invoices:read、trips:read、rates:read |
| 完整操作 | 以上全部 + invoices:write、trips:write、rates:write、settings:write |

記下 Token 值（格式：`1|xxxxxxxx`），**只顯示一次**。

---

## 步驟二：連線設定

### Claude Desktop（電腦版）

1. 開啟 Claude Desktop → 左上角 **Claude** 選單 → **Settings**
2. 點左側 **Developer** → **Edit Config**
3. 將以下內容貼入設定檔，替換 Token：

```json
{
  "mcpServers": {
    "invoice-system": {
      "command": "npx",
      "args": [
        "mcp-remote",
        "https://trunk.0921515408.com/mcp",
        "--header",
        "Authorization: Bearer 請貼上你的Token"
      ]
    }
  }
}
```

4. 儲存後**完全關閉再重新開啟** Claude Desktop
5. 對話框右下角出現工具圖示即代表連線成功

> **需求**：Node.js（[下載](https://nodejs.org)）

---

### ChatGPT Desktop（電腦版）

1. 安裝 [ChatGPT Desktop App](https://chatgpt.com/download)（需 Plus 或以上方案）
2. 開啟設定檔（若不存在請手動建立）：
   - **macOS**：`~/Library/Application Support/com.openai.chat/mcp.json`
   - **Windows**：`%APPDATA%\com.openai.chat\mcp.json`
3. 貼入以下內容，替換 Token：

```json
{
  "mcpServers": {
    "invoice-system": {
      "command": "npx",
      "args": [
        "mcp-remote",
        "https://trunk.0921515408.com/mcp",
        "--header",
        "Authorization: Bearer 請貼上你的Token"
      ]
    }
  }
}
```

4. 儲存後**完全關閉再重新開啟** ChatGPT Desktop
5. 新對話中輸入 `/` 或點工具圖示，確認 `invoice-system` 已出現

> **需求**：Node.js（[下載](https://nodejs.org)）

---

### Claude App（手機版）

1. 開啟 Claude App → 右上角頭像 → **Settings**
2. 點選 **Connectors** → **Add custom connector**
3. 填入以下資訊：
   - **URL**：`https://trunk.0921515408.com/mcp`
   - **Header**：`Authorization: Bearer 請貼上你的Token`
4. 點 **Connect** 完成

---

## 使用方式

連線成功後，直接用自然語言操作：

- 「查詢甲公司 2026 年 5 月的請款單」
- 「幫我新增一筆行程：2026-05-23，台北到台中，陳師傅，整車，重量 15t」
- 「計算台北到台中整車的運費」
- 「確認請款單 ID 3」
