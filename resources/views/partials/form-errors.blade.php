@if ($errors->any())
    <div class="mb-4 rounded-2xl bg-red-100 px-4 py-3 text-red-700 dark:bg-red-950 dark:text-red-300">
        <ul class="list-disc pl-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
