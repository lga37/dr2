@if (session('status'))
<div class="bg-green-200 p-3 mb-2 rounded text-green-800" role="alert">
    <span class="block sm:inline">{{ session('status') }}</span>
  </div>
@endif