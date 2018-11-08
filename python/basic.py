#!/usr/bin/env python

import srt
import pprint
import imp
import re
import os
import inspect

import urllib, json

def AddMarkers(clip, displayShift):

    url = "http://localhost:8000/kesh/markers"
    response = urllib.urlopen(url)
    data = json.loads(response.read())

    for marker in data:
        print marker
        frame = (marker['startTime'] / 10) * 30
        duration = (marker['endTime'] - marker['startTime']) / 10 * 30;
        title = str(marker['idx']) + ': ' + marker['title']
        markerAdded = clip.AddMarker(frame, marker['color'].title(), title, marker['note'], duration)

        print marker['title']
        if markerAdded:
            print(displayShift + "  " + "Marked added to " + file_name)
        else:
            print(displayShift + "  " + "Marker was not added :-( to " + file_name)

        # lets add this clip to the timeline, too, with in and out markers set
        # first, let's get the current clip info
        properties = clip.GetClipProperty()
        print properties

        meta = clip.GetMetadata()
        print meta

        x = clip.SetClipProperty('Take', 'Take Test')
        x = clip.SetClipProperty('In', '00:00:04:00')
        clip.SetClipProperty('Out', '00:00:08:00')
        properties = clip.GetClipProperty()
        print properties['In']

        if project.GetTimelineCount() == 0:
            timeline = mediaPool.CreateTimelineFromClips('markers', clip)
        else:
            timeline = project.GetTimelineByIndex(1)
            project.SetCurrentTimeline(timeline)
            mediaPool.AppendToTimeline(clip)

        items = timeline.GetItemsInTrack('video', 1)
        for itemIndex in items:
            item = items[itemIndex]
            print item.GetName()
            # print item.GetClipProperty()
            print item.GetDuration()

        # pprint.pprint(clip, )
        # return
    # return



def DisplayFolderInfo(folder, displayShift):
    path = folder.GetName()
    print(displayShift + "- " + path)
    clips = folder.GetClips()
    for clipIndex in clips:


        clip = clips[clipIndex]
        file_name = clip.GetClipProperty("File Name")["File Name"]


        prop = clip.GetClipProperty()
        print("Got a clip!")
        x = dir(clip)
        pprint.pprint(x)

        if 'MOV' in file_name:

            frames = int(prop['Frames']);

            # pprint.pprint(prop)
            for key, value in prop.items():
                if value:
                    print key.rjust(30), value

                # print key.rjust(10), value

#             return
            # AddMarkers(clip, displayShift)


            markers = clip.GetMarkers()
            for key, value in markers.iteritems():
                # clip.RemoveMarker(value)
                pprint.pprint(value)


            # break
        # print clip.GetMediaId()




    displayShift = "  " + displayShift

    folders = folder.GetSubFolders()
    for folderIndex in folders:
        DisplayFolderInfo(folders[folderIndex], displayShift)
    return


def DisplayMediaPoolInfo(project):
    mediaPool = project.GetMediaPool()
    print("- Media pool")
    DisplayFolderInfo(mediaPool.GetRootFolder(), "  ")
    return

def XXAddMarkers():
    file = open('main-interview.srt', "r")
    srt_text = file.read()
    gen = srt.parse(srt_text)

    # pprint.pprint(list(gen))

    if False:
        for i in gen:
            print(i.start, i.content)
            pprint.pprint(i.content)


smodule = imp.load_dynamic('fusionscript', 'C:\\Program Files\\Blackmagic Design\\DaVinci Resolve\\fusionscript.dll')
resolve = smodule.scriptapp('Resolve')

projectManager = resolve.GetProjectManager()
mediaStorage = resolve.GetMediaStorage()

# get these from the database, or via twig
projectName = 'claire';

# create the project if it doesn't exist
project = projectManager.LoadProject(projectName)
if project is None:
    project = projectManager.CreateProject(projectName)


if project is None:
    raise ValueError('Unable to load / create ' + projectName)

# go through all the items in the media pool, not just the new ones
mediaPool = project.GetMediaPool()

filePath = "C:\\JUFJ\\temp\\" + projectName + ".fcpxml"
timeline = mediaPool.ImportTimelineFromFile(filePath)

exit(0)


clips = mediaStorage.AddItemsToMediaPool([
    "C:\\JUFJ\Kesh\kesh-2e.MOV"
])


# Master Pool?
folder = mediaPool.GetRootFolder()

# go through each clip and add the markers
clips = folder.GetClips()

for clipIndex in clips:

    clip = clips[clipIndex]

    # remove previous markers
    markers = clip.GetMarkers()
    for markerIndex in markers:
        marker = markers[markerIndex]
        print marker
        print clip.GetClipProperty()
        # clip.DeleteMarker(marker)

    file_name = clip.GetClipProperty("File Path")["File Path"]
    # pprint.pprint(clip.GetClipProperty);

    AddMarkers(clip, '')

timeline = mediaPool.CreateEmptyTimeline('markers')
print(timeline)

for markerIndex in markers:
    print "Adding Marker to timeline"
    marker = markers[markerIndex]
    # mediaPool.AppendToTimeline(marker)
    dir(marker)
    pprint.pprint(marker)

project = projectManager.GetCurrentProject()





# Get each clip in the Media Pool
# project = projectManager.GetCurrentProject()
# DisplayMediaPoolInfo(project)


