#!/usr/bin/env python3
"""Refresh the LifeTech issue form from public GitHub ecosystem repositories."""
import json
import os
from pathlib import Path
import re
from urllib.parse import urlencode
from urllib.request import Request, urlopen

FORM = Path('.github/ISSUE_TEMPLATE/package-issue.yml')
API = 'https://api.github.com'


def request_json(path, params):
    url = f'{API}{path}?{urlencode(params)}'
    headers = {
        'Accept': 'application/vnd.github+json',
        'User-Agent': 'LifeTech-issue-dropdown-sync',
        'X-GitHub-Api-Version': '2022-11-28',
    }
    token = os.getenv('GITHUB_TOKEN')
    if token:
        headers['Authorization'] = f'Bearer {token}'
    with urlopen(Request(url, headers=headers), timeout=30) as response:
        return json.load(response)


def owner_repos(owner):
    results = []
    for page in range(1, 101):
        batch = request_json(f'/users/{owner}/repos', {'per_page': 100, 'page': page, 'type': 'owner'})
        if not isinstance(batch, list):
            raise RuntimeError(f'Invalid repository response for {owner}')
        results.extend(batch)
        if len(batch) < 100:
            return results
    raise RuntimeError(f'Too many repository pages for {owner}')


def search_repos(query):
    results = []
    # GitHub Search API caps search results at 1,000 per query.
    for page in range(1, 11):
        payload = request_json('/search/repositories', {'q': query, 'per_page': 100, 'page': page})
        batch = payload.get('items')
        if not isinstance(batch, list):
            raise RuntimeError(f'Invalid search response for {query}')
        results.extend(batch)
        if len(batch) < 100:
            break
    return results


def eligible(repo):
    return (not repo.get('private') and not repo.get('archived')
            and not repo.get('disabled') and not repo.get('fork'))


def main():
    found = {}
    # Include all first-party repositories, plus known separately hosted plugins.
    for owner in ('lifetechOCMS', 'ajayi-abolore'):
        for repo in owner_repos(owner):
            if not eligible(repo):
                continue
            if owner.lower() == 'lifetechocms' or repo['name'].lower().startswith(('plgmy', 'themelifetech')):
                found[repo['full_name'].lower()] = repo['full_name']

    # Discover packages under any owner through the standard topic or a clear name/description mention.
    for query in ('topic:lifetechocms', 'lifetechocms in:name,description'):
        for repo in search_repos(query):
            if eligible(repo):
                found[repo['full_name'].lower()] = repo['full_name']

    names = sorted(found.values(), key=str.casefold)
    if 'lifetechocms/lifetechocms' not in found:
        raise RuntimeError('Core repository missing; refusing to overwrite dropdown')
    if len(names) > 299:
        raise RuntimeError('Too many repositories for GitHub issue-form dropdown (maximum 300 options including Other)')

    original = FORM.read_text(encoding='utf-8')
    pattern = r'(?m)^  - type: (?:input|dropdown)\r?\n    id: repository-url\r?\n(?:(?!^  - type: ).*\r?\n?)*'
    matches = list(re.finditer(pattern, original))
    if len(matches) != 1:
        raise RuntimeError('Expected exactly one repository-url field')
    options = '\n'.join(f'        - "{name}"' for name in names + ['Other / Not listed'])
    replacement = (
        '  - type: dropdown\n'
        '    id: repository-url\n'
        '    attributes:\n'
        '      label: Affected Repository\n'
        '      description: Select the repository associated with this issue.\n'
        '      options:\n' + options + '\n'
        '    validations:\n'
        '      required: true\n\n'
    )
    updated = original[:matches[0].start()] + replacement + original[matches[0].end():]
    if updated != original:
        FORM.write_text(updated, encoding='utf-8')
        print(f'Updated dropdown with {len(names)} repositories')
    else:
        print(f'Already up to date ({len(names)} repositories)')


if __name__ == '__main__':
    main()
