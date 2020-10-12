using System;
using System.IO;
using Microsoft.AspNetCore;
using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Hosting;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.FileProviders;
using Microsoft.Extensions.Hosting;

namespace MantisBTPeachPie.Server
{
    class Program
    {
        static void Main(string[] args)
        {
            var host = WebHost.CreateDefaultBuilder(args)
                .UseStartup<Startup>()
                .Build();

            host.Run();
        }
    }

    class Startup
    {
        public void ConfigureServices(IServiceCollection services)
        {
            // Adds a default in-memory implementation of IDistributedCache.
            services.AddDistributedMemoryCache();

            services.AddSession(options =>
            {
                options.IdleTimeout = TimeSpan.FromMinutes(30);
                options.Cookie.HttpOnly = true;
            });

            services.AddPhp(options =>
            {

            });
        }

        static string ResolvePhpRoot(IWebHostEnvironment env)
        {
            const string prefix = "mantisbt"; // our directory name for mantisBT content files

            if (env.IsDevelopment())
            {
                // use local files when development
                // ../MantisBTPeachpie/
                var path = Path.GetFullPath(Path.Combine(env.ContentRootPath, "../MantisBTPeachpie"));
                if (Directory.Exists(path))
                {
                    return path;
                }
            }

            // default
            return Path.Combine(env.ContentRootPath, prefix);
        }

        public void Configure(IApplicationBuilder app, IWebHostEnvironment env)
        {
            if (env.IsDevelopment())
            {
                app.UseDeveloperExceptionPage();
            }

            app.UseSession();

            var phproot = ResolvePhpRoot(env);
            app.UsePhp(default, rootPath: phproot);
            app.UseStaticFiles(new StaticFileOptions { FileProvider = new PhysicalFileProvider(phproot), });

            app.UseDefaultFiles();
            app.UseStaticFiles();
        }
    }
}
