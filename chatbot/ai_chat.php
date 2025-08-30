<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduPlusAI Chatbot</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs"></script>
</head>
<body class="bg-gray-100 text-gray-900 flex">

  <!-- Sidebar -->
  <?php include("../assets/components/sidebar.php"); ?>

  <!-- Main Chat Section -->
  <div class="flex-1 flex flex-col">
    <?php include("../assets/components/header.php"); ?>

    <main class="flex-1 p-6 flex flex-col" x-data="{messages: []}">
      <h1 class="text-2xl font-bold mb-4">🤖 EduPlusAI Chat Assistant</h1>

      <!-- Chat Window -->
      <div id="chatWindow" class="flex-1 bg-white rounded-lg shadow p-4 overflow-y-auto space-y-3">
        <template x-for="msg in messages" :key="msg.id">
          <div :class="msg.sender === 'user' ? 'text-right' : 'text-left'">
            <div :class="msg.sender === 'user' 
                ? 'bg-blue-500 text-white inline-block px-3 py-2 rounded-lg' 
                : 'bg-gray-200 text-gray-800 inline-block px-3 py-2 rounded-lg'">
              <span x-text="msg.text"></span>
            </div>
          </div>
        </template>
      </div>

      <!-- Input Box -->
      <form id="chatForm" class="mt-4 flex">
        <input id="userInput" name="message" type="text" placeholder="Ask me anything..." 
               class="flex-1 border p-3 rounded-l-lg focus:ring-2 focus:ring-blue-400" required>
        <button type="submit" class="bg-blue-600 text-white px-6 rounded-r-lg hover:bg-blue-700 transition">
          Send
        </button>
      </form>
    </main>
  </div>

  <script>
    const form = document.getElementById("chatForm");
    const input = document.getElementById("userInput");
    const chatWindow = document.getElementById("chatWindow");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      let message = input.value;

      // Display user message
      addMessage("user", message);

      // Send to backend
      let res = await fetch("ai_process.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({message})
      });
      let data = await res.json();

      // Display AI reply
      addMessage("bot", data.reply);
      input.value = "";
    });

    function addMessage(sender, text) {
      let msgDiv = document.createElement("div");
      msgDiv.className = sender === "user" ? "text-right" : "text-left";

      let bubble = document.createElement("div");
      bubble.className = sender === "user" 
        ? "bg-blue-500 text-white inline-block px-3 py-2 rounded-lg"
        : "bg-gray-200 text-gray-800 inline-block px-3 py-2 rounded-lg";
      bubble.textContent = text;

      msgDiv.appendChild(bubble);
      chatWindow.appendChild(msgDiv);
      chatWindow.scrollTop = chatWindow.scrollHeight;
    }
  </script>
</body>
</html>
