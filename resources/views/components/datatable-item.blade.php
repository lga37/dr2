<div class="flex items-center"> 

    <span class="text-xs cursor-pointer font-medium leading-4 tracking-wider text-gray-500 uppercase">{{ $columnName }}</span>
    

    @if ($sortColumn != $columnName)
        <span>&#10607;</span>
    @elseif ($sortDirection=='ASC')
        <span>&#10597;</span>
    @else
        <span>&#10595;</span>
    @endif

</div>