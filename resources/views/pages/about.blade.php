@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1.05fr,0.95fr]">
            <div class="mesh-card px-8 py-10 reveal-up">
                <p class="section-kicker text-sm font-semibold uppercase tracking-[0.28em]">About Us</p>
                <h1 class="display-font mt-4 text-4xl font-bold text-slate-950">{{ $schoolSettings['school_name'] ?? 'BELOVED SCHOOLS' }}</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">Raising disciplined, knowledgeable, and Godly students for a purposeful future.</p>
                <p class="mt-5 text-sm leading-7 text-slate-600">BELOVED SCHOOLS was established in 2006 with a divine vision to impact the lives of young people through quality education and strong spiritual guidance. Located at Ayeteju Street, Ore, Ondo State, the school has grown into a respected institution known for discipline, excellence, and moral uprightness.</p>
                <p class="mt-4 text-sm leading-7 text-slate-600">From the beginning, the mission has been clear: to raise a generation of students who are not only academically sound but also morally grounded and God-fearing. The founders believed that education should shape both the mind and character, and this belief continues to guide the school today.</p>
            </div>

            <div class="section-card reveal-up">
                <h2 class="display-font text-2xl font-bold text-slate-950">School Identity</h2>
                <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-slate-900">Mission:</span> To provide quality education that develops students academically, morally, and spiritually, equipping them to succeed and make meaningful contributions to society.</p>
                    <p><span class="font-semibold text-slate-900">Vision:</span> To be a leading institution in raising disciplined, knowledgeable, and Godly individuals who will impact nations positively.</p>
                    <p><span class="font-semibold text-slate-900">Motto:</span> {{ $schoolSettings['motto'] ?? 'Knowledge for All Nation' }}</p>
                    <p><span class="font-semibold text-slate-900">Location:</span> {{ $schoolSettings['school_address'] ?? 'Ayeteju Street, Ore, Ondo State' }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['title' => 'Discipline', 'copy' => 'We nurture students to live with order, respect, and self-control in school and beyond.'],
                ['title' => 'Excellence', 'copy' => 'We pursue high standards in teaching, learning, and character formation.'],
                ['title' => 'Integrity', 'copy' => 'Truthfulness, honesty, and responsibility remain central to our school culture.'],
                ['title' => 'Godliness', 'copy' => 'Students are guided with strong spiritual and moral values for a purposeful life.'],
                ['title' => 'Responsibility', 'copy' => 'We train students to become dependable leaders in their homes, communities, and nation.'],
                ['title' => 'Founders', 'copy' => 'BELOVED SCHOOLS was founded by Mr. Zebilon K. S. alongside his wife Mrs. Grace Zebilon, whose passion for education and youth development shaped the school vision.'],
            ] as $card)
                <div class="section-card reveal-up">
                    <h2 class="display-font text-2xl font-bold text-slate-950">{{ $card['title'] }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['copy'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
