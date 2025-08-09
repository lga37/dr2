@if (session('status'))
<div class="bg-blue-200 p-3 mb-2 rounded text-blue-800" role="alert">
    <span class="block sm:inline">{{ session('status') }}</span>
  </div>
@endif

@if (session('success'))
<div class="bg-green-200 p-3 mb-2 rounded text-green-800" role="alert">
    <span class="block sm:inline">{{ session('success') }}</span>
  </div>
@endif

@if (session('error'))
<div class="bg-red-200 p-3 mb-2 rounded text-red-800" role="alert">
    <span class="block sm:inline">{{ session('error') }}</span>
  </div>
@endif



