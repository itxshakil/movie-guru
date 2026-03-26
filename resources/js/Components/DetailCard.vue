<template>
    <div class="flex min-h-full items-stretch justify-center text-center md:items-center">
        <div class="flex text-left text-base w-full">
            <div
                class="relative rounded-xl flex w-full items-center overflow-hidden bg-white dark:bg-gray-950 dark:text-white shadow-2xl">

                <div v-if="detail" itemscope itemtype="https://schema.org/Movie"
                     class="relative grid w-full grid-cols-1 items-start gap-x-6 gap-y-4 sm:grid-cols-12 lg:gap-x-8 p-4 sm:p-6 lg:p-8">
                    <div
                        class="relative overflow-hidden rounded-xl sm:col-span-4 lg:col-span-4 group/poster shadow-2xl shadow-black/30">
                        <div class="bg-gray-100 dark:bg-gray-900 aspect-2/3">
                            <img :alt="detail.Title + ' Poster'" itemprop="image"
                                 :src="moviePoster(detail)"
                                 class="object-cover object-center cursor-pointer w-full h-full"
                                 @error="handlePosterError">
                        </div>
                        <div class="flex flex-wrap gap-1 absolute top-0 p-2 justify-end w-full">
                            <WatchlistButton
                                :movie="{ imdb_id: detail.imdbID, title: detail.Title, year: detail.Year, poster: detail.Poster, type: detail.Type, imdb_rating: detail.imdbRating }"/>
                            <button
                                class="flex items-center gap-1 bg-white/20 hover:bg-white/40 active:scale-90 backdrop-blur-md text-white text-xs font-medium px-2.5 py-0.5 rounded-sm transition-all"
                                title="Share"
                                @click="shareMovie"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                                Share
                            </button>
                        <span v-if="trending"
                              class="flex items-center gap-1 bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-orange-900 dark:text-orange-300">
                            Trending
                            <svg class="h-4 w-4" fill="none" height="24" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none" stroke="none"/>
                                <path d="M3 17l6 -6l4 4l8 -8"/>
                                <path d="M14 7l7 0l0 7"/>
                            </svg>
                        </span>
                            <span v-else-if="recentlyReleased"
                                  class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-purple-900 dark:text-purple-300">New</span>
                            <span v-else-if="topRated"
                                  class="flex items-center gap-1 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-primary-900 dark:text-primary-300">
                        <svg class="w-4 h-4" fill="none" height="24" stroke="currentColor"
                             stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                             width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z"
                                  fill="none"
                                  stroke="none"/>
                            <path
                                d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                            <path
                                d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/><path
                            d="M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z"/></svg>
                                    Top Rated
                                </span>
                            <span v-else-if="hiddenGems"
                                  class="flex items-center gap-1 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-sm dark:bg-primary-900 dark:text-primary-300">
                                    <svg class="w-4 h-4" fill="none" height="24" stroke="currentColor"
                                         stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         viewBox="0 0 24 24"
                                         width="24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 0h24v24H0z"
                                              fill="none"
                                              stroke="none"/>
                                        <path
                                            d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                                        <path
                                            d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z"/><path
                                        d="M12.5 13.847l-1.5 1.153l.532 -1.857l-1.532 -1.143h1.902l.598 -1.8l.598 1.8h1.902l-1.532 1.143l.532 1.857z"/></svg>
                                    Hidden Gem
                                </span>
                        </div>
                    </div>
                    <div class="sm:col-span-8 lg:col-span-8">
                        <h1
                            class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white leading-tight tracking-tight"
                            itemprop="name"
                            style="font-feature-settings: 'kern' 1, 'liga' 1;"
                            v-text="detail.Title"
                        />
                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span
                                :title="detail.Released ? 'Released on '+ detail.Released : 'Release Year'"
                                class="text-sm font-semibold text-gray-500 dark:text-gray-400"
                                itemprop="datePublished"
                                v-text="detail.Year"
                            />
                            <span v-if="isValue(Genre)" class="text-gray-300 dark:text-gray-600">·</span>
                            <span v-if="isValue(Genre)" class="text-sm text-gray-500 dark:text-gray-400"
                                  itemprop="genre" title="Genre" v-text="Genre"/>
                            <span v-if="detail.Type !== 'series' && runtime"
                                  class="text-gray-300 dark:text-gray-600">·</span>
                            <span v-if="detail.Type !== 'series'" class="text-sm text-gray-500 dark:text-gray-400"
                                  itemprop="duration" title="Runtime" v-text="runtime"/>
                            <span v-if="isValue(detail.Rated) && detail.Rated !== 'Not Rated'"
                                  class="ml-1 text-[10px] font-black tracking-wider uppercase px-1.5 py-0.5 rounded border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400"
                                  itemprop="contentRating" v-text="detail.Rated"/>
                        </div>

                        <section aria-labelledby="information-heading" class="mt-4">
                            <h3 id="information-heading" class="sr-only">Product information</h3>
                            <div class="mb-3" itemprop="aggregateRating" itemscope
                                 itemtype="https://schema.org/AggregateRating">
                                <div v-if="basicRating" class="flex items-center">
                                    <svg
                                        v-for="i in 5"
                                        :key="i"
                                        :class="i <= basicRating ? 'text-yellow-500 dark:text-yellow-400' : 'text-gray-300 dark:text-gray-400'"
                                        aria-hidden="true" class="h-6 w-6 shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path clip-rule="evenodd"
                                            d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                            fill-rule="evenodd"/>
                                    </svg>

                                    <!--                                TODO: Redirect link to imdbID to rating page -->
                                    <a :href="'https://www.imdb.com/title/' + detail.imdbID + '/reviews'"
                                       class="ml-3 text-sm font-medium text-primary-800 dark:text-primary-200 hover:text-primary-600 dark:hover:text-primary-400"
                                       rel="noopener noreferrer"
                                       target="_blank"
                                    >
                                    <span class="sr-only" itemprop="ratingCount">{{
                                        isValue(detail.imdbVotes) ? detail.imdbVotes.replaceAll(',', '').trim() : 0
                                      }}</span>
                                        <span class="text-xs hover:underline">(<span>{{
                                        isValue(detail.imdbVotes) ? detail.imdbVotes : 'No '
                                      }}</span> reviews)</span>
                                  </a>

                                </div>
                              <small v-if="isValue(isValue(detail.imdbVotes))" class="ml-1"><strong
                                  itemprop="ratingValue">{{ basicRating }}</strong> out of <strong
                                  itemprop="bestRating">5</strong> stars</small>
                              <small v-else class="ml-1"><strong itemprop="ratingValue">No Rating Yet</strong></small>
                            </div>

                          <!-- Watch Now Section -->
                            <section class="mt-3">
                                <h3 class="text-xs font-black tracking-widest uppercase text-gray-400 dark:text-gray-500 mb-2">
                                    Where to Watch</h3>
                                <ul class="flex flex-wrap items-center gap-1.5">
                                    <SourceCard
                                        v-for="source in uniqueSources"
                                        :key="source.availability.sourceId"
                                        :send-analytics="sendAnalytics"
                                        :source="source"
                                        class="flex grow items-center gap-2 p-2 border rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors duration-150"
                                    >
                                    </SourceCard>

                                    <li v-if="affiliateLink"
                                        class="flex grow items-center gap-2 p-3 border-2 border-primary-500 rounded-xl shadow-lg bg-gradient-to-br from-primary-50 to-white dark:from-primary-950/40 dark:to-gray-900 transition-colors duration-150 group"
                                    >
                                        <a
                                            :href="affiliateLink.link"
                                            class="flex items-center gap-4 flex-1"
                                            rel="noopener noreferrer"
                                            target="_blank"
                                            @click="sendAnalytics('Affiliate: ' + affiliateLink.title, affiliateLink.link)"
                                        >
                                            <div
                                                class="h-12 w-12 flex items-center justify-center bg-primary-600 text-white rounded-lg shadow-md group-hover:bg-primary-500 transition-colors">
                                                <svg class="h-7 w-7" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"></path>
                                                </svg>
                                            </div>

                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="font-bold text-lg text-primary-900 dark:text-primary-100 leading-tight">
                                                        Book Tickets: {{ affiliateLink.title }}
                                                    </p>
                                                    <svg
                                                        class="h-5 w-5 text-primary-500 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round"
                                                              stroke-linejoin="round" stroke-width="2"/>
                                                    </svg>
                                                </div>

                                                <p class="text-sm text-primary-700/80 dark:text-primary-300/80 mt-0.5 font-medium">
                                                    Secure your seats for <span
                                                    class="text-primary-900 dark:text-primary-100 font-semibold">{{
                                                        detail.Title
                                                    }}</span> now!
                                                </p>
                                            </div>
                                        </a>
                                    </li>

                                    <li v-if="youtubeSearchLink"
                                        class="flex grow items-center gap-2 p-2 border rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
                                    >
                                        <a
                                            :href="youtubeSearchLink"
                                            class="flex items-center gap-3 flex-1"
                                            rel="noopener noreferrer"
                                            target="_blank"
                                            @click="sendAnalytics('Watch on YouTube', youtubeSearchLink)"
                                        >
                                            <div
                                                class="h-10 w-10 flex items-center justify-center bg-red-600 rounded text-white shrink-0">
                                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">Watch on
                                                    YouTube</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Search on
                                                    YouTube</p>
                                            </div>
                                        </a>
                                    </li>

                                    <li
                                        class="flex grow items-center gap-2 p-2 border rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
                                    >
                                        <a
                                            :href="searchFallbackLink.url"
                                            class="flex items-center gap-3 flex-1"
                                            rel="noopener noreferrer"
                                            target="_blank"
                                            @click="sendAnalytics(searchFallbackLink.platform + ' Search', searchFallbackLink.url)"
                                        >
                                            <div v-if="searchFallbackLink.platform === 'YouTube'"
                                                 class="h-10 w-10 flex items-center justify-center bg-red-600 rounded text-white shrink-0">
                                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                </svg>
                                            </div>
                                            <img v-else alt="Google Logo" class="h-10 w-10 rounded"
                                                 src="/assets/google-logo.png"/>

                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ searchFallbackLink.platform }} Search
                                                </p>

                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Search on {{ searchFallbackLink.platform }}
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </section>

                            <SnippetText :text="isValue(detail.Plot) ? detail.Plot : 'No Plot detail'"
                                         class="text-gray-700 dark:text-gray-300 mt-3 text-sm leading-relaxed"
                                         itemprop="description"/>

                            <div class="mt-4">
                                <h3 class="text-xs font-black tracking-widest uppercase text-gray-400 dark:text-gray-500 mb-2 capitalize">
                                    {{ detail.Type }} Details
                                </h3>
                                <div>
                                    <dl class="rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800/60">
                                        <div itemprop="actor" itemscope itemtype="https://schema.org/Person"
                                            v-if="isValue(detail.Actors)"
                                            class="p-2 sm:py-4 sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Actor
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0" itemprop="name"
                                                v-text="detail.Actors"></dd>
                                        </div>
                                        <div itemprop="writer" itemscope itemtype="https://schema.org/Person"
                                            v-if="isValue(detail.Writer)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Writer
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0" itemprop="name"
                                                v-text="detail.Writer"></dd>
                                        </div>
                                        <div itemprop="director" itemscope itemtype="https://schema.org/Person"
                                            v-if="isValue(detail.Director)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Director
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0" itemprop="name"
                                                v-text="detail.Director"></dd>
                                        </div>
                                        <div v-if="detail.Type === 'series' && isValue(detail.totalSeasons)"
                                             class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">No.
                                                of Seasons
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0">
                                                {{ detail.totalSeasons }} Seasons - <span itemprop="duration">{{ runtime }}</span>
                                            </dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Awards)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Awards
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Awards"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Country)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Country
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Country"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.BoxOffice)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">Box
                                                Office
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.BoxOffice"></dd>
                                        </div>
                                        <div
                                            v-if="isValue(detail.Production)"
                                            class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Production
                                            </dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-200 sm:col-span-3 sm:mt-0"
                                                v-text="detail.Production"></dd>
                                        </div>
                                        <div v-if="detail.Ratings.length > 1"
                                             class="p-2 sm:py-4  sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-100 dark:odd:bg-gray-800">
                                            <dt class="text-sm font-medium leading-6 text-gray-900 dark:text-white">
                                                Ratings
                                            </dt>
                                            <dd class="mt-2 text-sm text-gray-900 sm:col-span-3 sm:mt-0">
                                                <ul class="divide-y divide-gray-100 rounded-md border border-gray-200"
                                                    role="list">
                                                    <li v-for="(rating, index) in detail.Ratings" :key="index"
                                                        class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
                                                        <span class="shrink-0 text-gray-900 dark:text-gray-200"
                                                              v-text="rating.Source"></span>
                                                        <span
                                                            class="font-medium  text-primary-600 hover:text-primary-100"
                                                            v-text="rating.Value"></span>
                                                    </li>
                                                </ul>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

              <DetailSkeletonCard v-else :title="title"/>
            </div>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="affiliateLink"
             class="fixed bottom-0 left-0 right-0 z-50 p-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-t border-primary-200 dark:border-primary-800 shadow-2xl md:hidden"
        >
            <a
                :href="affiliateLink.link"
                class="flex items-center justify-center gap-2 w-full py-3.5 px-6 bg-primary-600 hover:bg-primary-500 active:bg-primary-700 text-white font-bold text-base rounded-xl shadow-lg transition-colors"
                rel="noopener noreferrer"
                target="_blank"
                @click="sendAnalytics('Affiliate Sticky: ' + affiliateLink.title, affiliateLink.link)"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                Book Tickets: {{ affiliateLink.title }}
            </a>
        </div>
    </Teleport>
</template>
<script setup>
import DetailSkeletonCard from '@/Components/DetailSkeletonCard.vue';
import {computed, inject} from 'vue';
import SnippetText from '@/Components/SnippetText.vue';
import SourceCard from "@/Components/SourceCard.vue";
import WatchlistButton from "@/Components/WatchlistButton.vue";

const gtag = inject('gtag');

const props = defineProps({
    detail: Object,
    sources: {
        type: Array,
        required: false,
        default: () => [],
    },
    title: {
    type: String,
    required: false,
    },
    affiliateLink: {
        type: Object,
        required: false,
        default: null,
  }
});

const emit = defineEmits(['share']);

const uniqueSources = computed(() => {
    const sourceMap = new Map();

    if (!Array.isArray(props.sources)) {
        return [];
    }

    props.sources.forEach((source) => {
        if (!source || !source.meta || !source.meta.name) {
            return; // skip invalid entries
        }

        const name = source.meta.name;
        const format = source?.availability?.format ?? null;

        if (sourceMap.has(name)) {
            const currentSource = sourceMap.get(name);
            if (format) {
                currentSource.formats = Array.isArray(currentSource.formats)
                    ? [...new Set([...currentSource.formats, format])]
                    : [format];
            }
        } else {
            sourceMap.set(name, {
                ...source,
                formats: format ? [format] : [],
            });
        }
    });

    return Array.from(sourceMap.values());
});


const associateTrackingID = "itxshakil0ec-21";
const netflixLink = computed(() => props.detail ? `https://www.netflix.com/search?q=${encodeURIComponent(props.detail.Title)}` : '');
const amazonAffiliateLink = computed(() => props.detail ? `https://primevideo.com?tag=${associateTrackingID}&searchTerm=${encodeURIComponent(props.detail.Title)}` : '');
// const huluLink = computed(() => props.detail ? `https://www.hulu.com/search?q=${encodeURIComponent(props.detail.Title)}` : '');
// const disneyPlusLink = computed(() => props.detail ? `https://www.disneyplus.com/search?q=${encodeURIComponent(props.detail.Title)}` : '');
// const hboMaxLink = computed(() => props.detail ? `https://play.hbomax.com/search?q=${encodeURIComponent(props.detail.Title)}` : '');
const searchFallbackLink = computed(() => {
    if (!props.detail?.imdbID) {
        return {url: '', platform: 'Google'};
    }
    const hash = props.detail.imdbID.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    const isYouTube = hash % 10 < 3;
    if (isYouTube) {
        return {
            url: `https://www.youtube.com/results?search_query=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie')}`,
            platform: 'YouTube',
        };
    }
    return {
        url: `https://www.google.com/search?q=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie watch online')}`,
        platform: 'Google',
    };
});

const youtubeSearchLink = computed(() => {
    if (!props.detail?.imdbID) {
        return null;
    }
    const currentYear = new Date().getFullYear();
    const movieYear = parseInt(props.detail.Year);
    if (isNaN(movieYear) || movieYear >= currentYear) {
        return null;
    }
    const hash = props.detail.imdbID.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    if (hash % 3 !== 0) {
        return null;
    }
    return `https://www.youtube.com/results?search_query=${encodeURIComponent(props.detail.Title + ' ' + (props.detail.Year ?? '') + ' full movie')}`;
});

const isValue = function(value) {
    return value && value !== 'N/A';
};

const basicRating = computed(() => {
  if (isValue(props.detail.imdbRating)) {
    let mappedRating = (props.detail.imdbRating / 2).toFixed(1);

    mappedRating = Math.max(.1, Math.min(5, mappedRating));

    return mappedRating;
  }

  return 'No Rating';
});

const topRated = computed(() => {
  if (!isValue(props.detail.imdbRating)) {
    return false;
  }

    const imdbVotes = parseInt(props.detail.imdbVotes.replaceAll(',', ''));
  return (props.detail.imdbRating >= 8.5 && imdbVotes > 80_000) || imdbVotes > 1_00_000 && props.detail.imdbRating >= 8;
});

const hiddenGems = computed(() => {
    const imdbVotes = parseInt(props.detail.imdbVotes.replaceAll(',', ''));
  return props.detail.imdbRating > 8.5 && imdbVotes < 80_000 && imdbVotes > 3_000;
});

const recentlyReleased = computed(() => {
    const releaseDate = new Date(props.detail.Released);
    const today = new Date();

    const diffTime = Math.abs(today - releaseDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    return diffDays < 30;
});
const trending = computed(() => {
    return topRated.value && recentlyReleased.value;
});

const Genre = computed(() => {
    return props.detail.Genre.replaceAll(', ', '/');
});

const runtime = computed(() => {
    const totalMinutes = parseInt(props.detail.Runtime);
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    let result = '';

    if (hours > 0) {
        result += hours + 'h';
    }

    if (minutes > 0) {
        if (result !== '') {
            result += ' ';
        }
        result += minutes + 'm';
    }

    return result;
});

const moviePoster = (movie) => {
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
};

const handlePosterError = (event) => {
    event.target.src = '/assets/images/no-poster.jpg';
    event.target.classList.add('opacity-40', 'grayscale');
};

function sendAnalytics(platform, link) {
    if (gtag) {
        gtag.trackExternalClick(platform, link);
  }
  console.log(`Analytics Event Sent: Platform - ${platform}, Link - ${link}`);
}

const shareMovie = async () => {
    emit('share');
};

</script>
