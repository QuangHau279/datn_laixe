@extends('layouts.app')

@section('content')
<div class="container max-w-2xl mx-auto my-6">
  <h2 class="text-xl font-semibold mb-3">Chatbox Lý thuyết lái xe</h2>

  <div id="out" class="border rounded p-3 bg-white min-h-[200px] max-h-80 overflow-auto"></div>

  <form id="f" class="mt-3 flex gap-2">
    <input id="q" class="flex-1 border rounded p-2"
           placeholder="Hỏi: 'câu 115', 'biển cấm dừng', 'mô phỏng'..." />
    <button class="px-3 py-2 rounded bg-blue-600 text-white" type="submit">Gửi</button>
  </form>
</div>

<script>
const API = @json(config('services.chat.api_url'));
const out = document.getElementById('out');

document.getElementById('f').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const q = document.getElementById('q').value.trim();
  if(!q) return;

  out.insertAdjacentHTML('beforeend', `<div class="mb-2"><b>Bạn:</b> ${q}</div>`);
  out.scrollTop = out.scrollHeight;

  try {
    const r = await fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: q })
    });
    const data = await r.json();
    const a = data?.answer || '[không có trả lời]';
    out.insertAdjacentHTML('beforeend', `<div class="mb-2"><b>AI:</b> ${a}</div>`);
  } catch (err) {
    out.insertAdjacentHTML('beforeend', `<div class="mb-2 text-red-600"><b>Lỗi:</b> ${err}</div>`);
  }

  out.scrollTop = out.scrollHeight;
});
</script>
@endsection
