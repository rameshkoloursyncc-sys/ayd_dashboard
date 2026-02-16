<aside id="sidebar" class="fixed top-0 left-0 z-20 flex flex-col flex-shrink-0 hidden w-64 h-full pt-16 font-normal duration-75 lg:flex transition-width" aria-label="Sidebar">
  <div class="relative flex flex-col flex-1 min-h-0 pt-0 bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700">
    <div class="flex flex-col flex-1 pt-5 pb-4 overflow-y-auto">
      <div class="flex-1 px-3 space-y-1 bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
        <ul class="pb-2 space-y-2">
          <li>
            @if (Auth::user()->role == 'medical_executive')
            <a href="{{ route('medical-executive.dashboard') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Dashboard</span>
            </a>
            @else
            <a href="{{ url('/') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Dashboard</span>
            </a>
            @endif
          </li>
          @if (Auth::user()->role == 'super_admin')
          <li>
            <a href="{{ route('superadmin.pharma-companies.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Pharma Companies</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.medical-executives.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Medical Executives</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.doctors.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Doctors</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.services.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Services</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.banners.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Banners</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.coupons.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Coupons</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.faq.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>FAQ</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.sponsors.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Sponsors</span>
            </a>
          </li>
          <li>
            <a href="{{ route('superadmin.patients.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Patients</span>
            </a>
          </li>
          @endif
          @if (Auth::user()->role == 'pharma_admin')
          <li>
            <a href="{{ route('pharma-admin.my-company.show') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>My Company</span>
            </a>
          </li>
          <li>
            <a href="{{ route('pharma-admin.medical-executives.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Medical Executives</span>
            </a>
          </li>
          <li>
            <a href="{{ route('pharma-admin.doctors.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Doctors</span>
            </a>
          </li>
          @endif
          @if (Auth::user()->role == 'medical_executive')
          <li>
            <a href="{{ route('medical-executive.doctors.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Doctors</span>
            </a>
          </li>
          {{-- Patients link commented out until route is implemented
          <li>
            <a href="{{ route('medical-executive.patients.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Patients</span>
            </a>
          </li>
          --}}
          @endif
          @if (Auth::user()->role == 'doctor')
          <li>
            <a href="{{ route('doctor.patients.index') }}" class="flex items-center p-2 text-base text-gray-900 rounded-lg hover:bg-gray-100 group dark:text-gray-200 dark:hover:bg-gray-700">
                <svg class="flex-shrink-0 w-6 h-6 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM9.009 17.008a.75.75 0 01.416.223l.516.516a.75.75 0 01-.002 1.06l-.516.516a.75.75 0 01-1.06-.002l-.516-.516a.75.75 0 01.002-1.06l.516-.516a.75.75 0 01.416-.223zM10 12a7 7 0 100 14 7 7 0 000-14zM10 10a5 5 0 110 10 5 5 0 010-10z" clip-rule="evenodd"></path></svg>
                <span class="ml-3" sidebar-toggle-item>Patients</span>
            </a>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</aside>