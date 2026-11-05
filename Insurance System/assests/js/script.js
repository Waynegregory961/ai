// AI chat handler (only runs on assistant page)
document.addEventListener('DOMContentLoaded', () => {
  const form  = document.getElementById('chat-form');
  if (!form) return;
  const log   = document.getElementById('chat-log');
  const input = document.getElementById('chat-input');

  const addBubble = (text, who, isError = false) => {
    const wrap = document.createElement('div');
    wrap.className = 'chat-msg ' + who + (isError ? ' error' : '');
    const bub = document.createElement('div');
    bub.className = 'bubble';
    bub.textContent = text;
    wrap.appendChild(bub);
    log.appendChild(wrap);
    log.scrollTop = log.scrollHeight;
  };

  addBubble('Hello! I am your insurance assistant. Ask me anything about policies or claims.', 'bot');

  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;
    addBubble(msg, 'user');
    input.value = '';

    const sendBtn = document.getElementById('chat-send');
    sendBtn.disabled = true;

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg, csrf }),
      });
      const data = await res.json();
      if (data.error) {
        addBubble(data.error, 'bot', true);
      } else {
        addBubble(data.reply, 'bot');
      }
    } catch (err) {
      addBubble('Connection problem. Please try again.', 'bot', true);
    } finally {
      sendBtn.disabled = false;
      input.focus();
    }
  });
});